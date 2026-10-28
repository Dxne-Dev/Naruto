<?php

namespace App\Http\Controllers;

use App\Models\Fighter;
use App\Models\IpTournamentQuota;
use App\Models\Tournament;
use App\Services\TournamentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function __construct(
        protected TournamentService $tournamentService
    ) {}

    /**
     * List all tournaments with open slots.
     */
    public function index(Request $request): View
    {
        $now = now();

        $allTournaments = Tournament::whereIn('status', [
            Tournament::STATUS_REGISTRATION,
            Tournament::STATUS_LOCKED,
            Tournament::STATUS_RUNNING,
            Tournament::STATUS_COMPLETED,
        ])
            ->with([
                'entries.fighter.user',
                'matches.fighterA.user',
                'matches.fighterB.user',
                'matches.winner',
                'matches.battle',
            ])
            ->withCount('entries')
            ->latest()
            ->get();

        $openTournaments = $allTournaments->where('status', Tournament::STATUS_REGISTRATION);
        $liveTournaments = $allTournaments->whereIn('status', [Tournament::STATUS_LOCKED, Tournament::STATUS_RUNNING]);
        $completedTournaments = $allTournaments->where('status', Tournament::STATUS_COMPLETED);

        $userFighter = auth()->check() ? auth()->user()->fighter : null;
        $myTournaments = $userFighter ? $allTournaments->filter(function ($t) use ($userFighter) {
            return $t->entries->contains('fighter_id', $userFighter->id);
        }) : collect();

        // Prochaine ouverture des inscriptions (Lundi, Mercredi, Vendredi à 08h00)
        $scheduledDays = [1, 3, 5]; // Mon, Wed, Fri
        $nextRegistrationDate = null;
        for ($i = 0; $i <= 7; $i++) {
            $candidateDate = $now->copy()->addDays($i)->setTime(8, 0, 0);
            if (in_array((int) $candidateDate->dayOfWeekIso, $scheduledDays, true)) {
                if ($candidateDate->gt($now)) {
                    $nextRegistrationDate = $candidateDate;
                    break;
                }
            }
        }

        return view('tournaments.index', [
            'openTournaments' => $openTournaments,
            'liveTournaments' => $liveTournaments,
            'completedTournaments' => $completedTournaments,
            'myTournaments' => $myTournaments,
            'userFighter' => $userFighter,
            'nextRegistrationDate' => $nextRegistrationDate,
        ]);
    }

    /**
     * Display the Hall of Fame (Panthéon des Shinobis Champions).
     */
    public function hallOfFame(): View
    {
        $championsLeaderboard = Fighter::whereHas('tournamentEntries', function ($q) {
            $q->where('final_position', 1);
        })
            ->withCount(['tournamentEntries as titles_count' => function ($q) {
                $q->where('final_position', 1);
            }])
            ->with('user')
            ->orderByDesc('titles_count')
            ->orderByDesc('level')
            ->take(15)
            ->get();

        $recentChampionships = Tournament::where('status', Tournament::STATUS_COMPLETED)
            ->with([
                'entries' => fn ($q) => $q->where('final_position', 1)->with('fighter.user'),
                'matches' => fn ($q) => $q->where('round', 3)->with('battle'),
            ])
            ->latest('updated_at')
            ->paginate(12);

        return view('tournaments.hall_of_fame', [
            'championsLeaderboard' => $championsLeaderboard,
            'recentChampionships' => $recentChampionships,
        ]);
    }

    /**
     * Show tournament details and bracket.
     */
    public function show(Tournament $tournament): View
    {
        $tournament->load([
            'entries.fighter.user',
            'matches.fighterA.user',
            'matches.fighterB.user',
            'matches.winner',
            'matches.battle',
        ]);

        $userFighter = auth()->check() ? auth()->user()->fighter : null;
        $isRegistered = $userFighter
            ? $tournament->entries->contains('fighter_id', $userFighter->id)
            : false;

        $round1Matches = $tournament->matches->where('round', 1)->values();
        $round2Matches = $tournament->matches->where('round', 2)->values();
        $round3Matches = $tournament->matches->where('round', 3)->values();

        return view('tournaments.show', [
            'tournament' => $tournament,
            'userFighter' => $userFighter,
            'isRegistered' => $isRegistered,
            'round1Matches' => $round1Matches,
            'round2Matches' => $round2Matches,
            'round3Matches' => $round3Matches,
        ]);
    }

    /**
     * Register fighter to tournament.
     */
    public function register(Request $request, Tournament $tournament): RedirectResponse
    {
        $user = $request->user();
        if (! $user->fighter) {
            return redirect()->route('onboarding.index')
                ->with('error', 'Tu dois forger un combattant avant de t\'inscrire à un tournoi.');
        }

        // Enforce quota of 2 tournaments if microtransaction not paid
        $clientIp = $request->ip();
        $userAgent = $request->userAgent();
        $fingerprint = IpTournamentQuota::generateFingerprint($clientIp, $userAgent);

        if (! $user->canEnterTournament($clientIp, $userAgent)) {
            return redirect()->route('payment.checkout')
                ->with('error', 'Quota de 2 tournois gratuits atteint pour ce compte et cet appareil ! Débloque ton accès illimité via le Pass Shinobi (700 FCFA).');
        }

        try {
            $this->tournamentService->registerFighter(
                $tournament,
                $user->fighter,
                $clientIp,
                $fingerprint,
                $userAgent
            );

            return back()->with('success', "Ton combattant {$user->fighter->name} est inscrit au tournoi !");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Unregister fighter from tournament.
     */
    public function unregister(Request $request, Tournament $tournament): RedirectResponse
    {
        $user = $request->user();
        if (! $user->fighter) {
            return back()->with('error', 'Aucun combattant trouvé.');
        }

        try {
            $this->tournamentService->unregisterFighter($tournament, $user->fighter);

            return back()->with('info', 'Inscription annulée avec succès.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
