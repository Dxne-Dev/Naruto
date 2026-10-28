<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Models\Fighter;
use App\Models\MatchGame;
use App\Models\Tournament;
use App\Models\TournamentEntry;
use App\Services\BattleEngine;
use App\Services\BattleNarratorService;
use App\Services\GroqBattleNarratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BattleController extends Controller
{
    public function __construct(
        protected BattleEngine $battleEngine,
        protected BattleNarratorService $narratorService,
        protected GroqBattleNarratorService $groqNarratorService,
    ) {}

    /**
     * List combat history, user upcoming matches and community arena.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $fighter = $user?->fighter;

        // 1. User's specific upcoming match in an ongoing tournament
        $myUpcomingMatch = null;
        $myActiveTournamentEntry = null;

        if ($fighter) {
            $myUpcomingMatch = MatchGame::where('status', MatchGame::STATUS_PENDING)
                ->where(function ($q) use ($fighter) {
                    $q->where('fighter_a_id', $fighter->id)
                        ->orWhere('fighter_b_id', $fighter->id);
                })
                ->with(['fighterA.user', 'fighterB.user', 'tournament'])
                ->orderBy('scheduled_at', 'asc')
                ->first();

            // Check if user is registered in an open/active tournament
            $myActiveTournamentEntry = TournamentEntry::where('fighter_id', $fighter->id)
                ->whereHas('tournament', function ($q) {
                    $q->whereIn('status', ['registration', 'locked', 'in_progress']);
                })
                ->with('tournament')
                ->latest()
                ->first();
        }

        // 2. User's personal battle history
        $myBattles = $fighter
            ? Battle::where(function ($q) use ($fighter) {
                $q->where('fighter_a_id', $fighter->id)
                    ->orWhere('fighter_b_id', $fighter->id);
            })
                ->with(['fighterA.user', 'fighterB.user', 'winner', 'match.tournament'])
                ->latest()
                ->paginate(6, ['*'], 'my_page')
            : collect();

        // 3. User's latest battle to feature if available
        $myLastBattle = $fighter
            ? Battle::where(function ($q) use ($fighter) {
                $q->where('fighter_a_id', $fighter->id)
                    ->orWhere('fighter_b_id', $fighter->id);
            })
                ->with(['fighterA.user', 'fighterB.user', 'winner', 'match.tournament', 'logs.attacker'])
                ->latest()
                ->first()
            : null;

        // 4. Community Arena: all battles excluding or including community
        $communityBattlesQuery = Battle::query()
            ->with(['fighterA.user', 'fighterB.user', 'winner', 'match.tournament'])
            ->latest();

        // Featured community battle (the absolute latest or most high-stakes match)
        $featuredBattle = Battle::with(['fighterA.user', 'fighterB.user', 'winner', 'match.tournament', 'logs.attacker'])
            ->latest()
            ->first();

        // Other matches scheduled in tournaments
        $otherUpcomingMatches = MatchGame::where('status', MatchGame::STATUS_PENDING)
            ->whereNotNull('fighter_a_id')
            ->whereNotNull('fighter_b_id')
            ->when($fighter, function ($q) use ($fighter) {
                $q->where('fighter_a_id', '!=', $fighter->id)
                    ->where('fighter_b_id', '!=', $fighter->id);
            })
            ->with(['fighterA', 'fighterB', 'tournament'])
            ->orderBy('scheduled_at', 'asc')
            ->take(4)
            ->get();

        $communityBattles = $communityBattlesQuery->paginate(12, ['*'], 'arena_page');

        // Active open tournament for quick registration CTA if user is not yet registered
        $openTournament = Tournament::where('status', 'registration')
            ->withCount('entries')
            ->latest()
            ->first();

        return view('battles.index', [
            'fighter' => $fighter,
            'myUpcomingMatch' => $myUpcomingMatch,
            'myActiveTournamentEntry' => $myActiveTournamentEntry,
            'myBattles' => $myBattles,
            'myLastBattle' => $myLastBattle,
            'featuredBattle' => $featuredBattle,
            'otherUpcomingMatches' => $otherUpcomingMatches,
            'communityBattles' => $communityBattles,
            'openTournament' => $openTournament,
        ]);
    }

    /**
     * Display a detailed battle recap and turn-by-turn playback.
     */
    public function show(Battle $battle): View
    {
        $battle->load([
            'fighterA.user',
            'fighterB.user',
            'winner',
            'match.tournament',
            'logs.attacker',
        ]);

        $narrativeRounds = $this->narratorService->buildNarrativeRounds(
            $battle->logs,
            $battle->fighterA,
            $battle->fighterB
        );

        // Fetch or dynamically generate with Groq AI commentary
        $aiCommentary = $this->groqNarratorService->getOrGenerateCommentary($battle);

        // Build replay rounds array for real-time typewriter playback
        $fA = $battle->fighterA;
        $fB = $battle->fighterB;
        $maxHpA = $fA ? $fA->getMaxHp() : 820;
        $maxHpB = $fB ? $fB->getMaxHp() : 780;
        $maxChakraA = $fA ? $fA->getMaxChakra() : 500;
        $maxChakraB = $fB ? $fB->getMaxChakra() : 500;

        $groupedLogs = $battle->logs->groupBy('round_number');
        $parts = preg_split('/(?=## 🔴 TOUR \d+)/u', $aiCommentary ?? '');
        $introText = trim($parts[0] ?? '');

        // Extract verdict / conclusion if present in the last round
        $roundsTimeline = [];
        $roundsCount = $battle->rounds_count ?: count($groupedLogs);

        for ($r = 1; $r <= $roundsCount; $r++) {
            $roundRaw = $parts[$r] ?? '';
            $lastLog = $groupedLogs->get($r)?->last();

            $hpA = $lastLog ? $lastLog->hp_remaining_a : $maxHpA;
            $hpB = $lastLog ? $lastLog->hp_remaining_b : $maxHpB;

            // Chakra remaining (with fallback calculation if battle was simulated with old zero-chakra records)
            $chkA = $lastLog?->chakra_remaining_a;
            $chkB = $lastLog?->chakra_remaining_b;
            if ($chkA === null || ($chkA === 0 && $r === 1 && $maxChakraA > 0)) {
                $chkA = max(0, $maxChakraA - ($r * 25));
            }
            if ($chkB === null || ($chkB === 0 && $r === 1 && $maxChakraB > 0)) {
                $chkB = max(0, $maxChakraB - ($r * 20));
            }

            $roundsTimeline[] = [
                'round' => $r,
                'content' => trim($roundRaw),
                'hp_a' => $hpA,
                'hp_b' => $hpB,
                'chakra_a' => $chkA,
                'chakra_b' => $chkB,
            ];
        }

        $hasAiCommentary = ! empty($aiCommentary);

        return view('battles.show', [
            'battle' => $battle,
            'fighterA' => $battle->fighterA,
            'fighterB' => $battle->fighterB,
            'winner' => $battle->winner,
            'logs' => $battle->logs,
            'narrativeRounds' => $narrativeRounds,
            'aiCommentary' => $aiCommentary,
            'hasAiCommentary' => $hasAiCommentary,
            'introText' => $introText,
            'roundsTimeline' => $roundsTimeline,
            'maxHpA' => $maxHpA,
            'maxHpB' => $maxHpB,
            'maxChakraA' => $maxChakraA,
            'maxChakraB' => $maxChakraB,
        ]);
    }

    /**
     * Instant 1v1 training duel outside of tournaments.
     */
    public function trainingDuel(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user?->fighter) {
            return redirect()->route('fighters.create')
                ->with('error', 'Crée un combattant pour lancer un duel d\'entraînement.');
        }

        // Pick a random opponent
        $opponent = Fighter::where('id', '!=', $user->fighter->id)->inRandomOrder()->first();

        if (! $opponent) {
            return back()->with('error', 'Aucun adversaire disponible dans l\'arène pour l\'instant.');
        }

        $battle = $this->battleEngine->simulate($user->fighter, $opponent);

        return redirect()->route('battles.show', $battle)
            ->with('success', 'Combat d\'entraînement simulé avec succès !');
    }
}
