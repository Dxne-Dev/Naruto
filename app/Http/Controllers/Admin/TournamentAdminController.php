<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fighter;
use App\Models\Tournament;
use App\Models\User;
use App\Services\FighterGenerator;
use App\Services\TournamentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TournamentAdminController extends Controller
{
    public function __construct(
        protected TournamentService $tournamentService,
        protected FighterGenerator $fighterGenerator
    ) {}

    /**
     * Display all tournaments with GM controls.
     */
    public function index(): View
    {
        $tournaments = Tournament::withCount('entries')->latest()->paginate(15);

        return view('admin.tournaments.index', [
            'tournaments' => $tournaments,
        ]);
    }

    /**
     * Show form to create a new tournament.
     */
    public function create(): View
    {
        return view('admin.tournaments.create');
    }

    /**
     * Store a new tournament.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:draft,registration,locked,running,completed,cancelled'],
            'registration_opens_at' => ['nullable', 'date'],
            'registration_closes_at' => [
                'nullable',
                'date',
                'after:registration_opens_at',
            ],
            'starts_at' => [
                'nullable',
                'date',
                'after_or_equal:registration_closes_at',
            ],
        ], [
            'registration_closes_at.after' => 'La clôture des inscriptions doit obligatoirement être postérieure à la date d\'ouverture.',
            'starts_at.after_or_equal' => 'Le début des duels doit avoir lieu après ou au moment exact de la clôture des inscriptions.',
        ]);

        $tournament = Tournament::create($validated);

        return redirect()->route('admin.tournaments.edit', $tournament)
            ->with('success', "Tournoi \"{$tournament->title}\" créé avec succès !");
    }

    /**
     * Show GM tournament management cockpit.
     */
    public function edit(Tournament $tournament): View
    {
        $tournament->load([
            'entries.fighter.user',
            'matches.fighterA.user',
            'matches.fighterB.user',
            'matches.winner',
            'matches.battle',
        ]);

        $round1Matches = $tournament->matches->where('round', 1)->values();
        $round2Matches = $tournament->matches->where('round', 2)->values();
        $round3Matches = $tournament->matches->where('round', 3)->values();

        return view('admin.tournaments.edit', [
            'tournament' => $tournament,
            'round1Matches' => $round1Matches,
            'round2Matches' => $round2Matches,
            'round3Matches' => $round3Matches,
        ]);
    }

    /**
     * Update tournament settings.
     */
    public function update(Request $request, Tournament $tournament): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:draft,registration,locked,running,completed,cancelled'],
            'registration_opens_at' => ['nullable', 'date'],
            'registration_closes_at' => [
                'nullable',
                'date',
                'after:registration_opens_at',
            ],
            'starts_at' => [
                'nullable',
                'date',
                'after_or_equal:registration_closes_at',
            ],
        ], [
            'registration_closes_at.after' => 'La clôture des inscriptions doit obligatoirement être postérieure à la date d\'ouverture.',
            'starts_at.after_or_equal' => 'Le début des duels doit avoir lieu après ou au moment exact de la clôture des inscriptions.',
        ]);

        $tournament->update($validated);

        return back()->with('success', 'Paramètres du tournoi mis à jour.');
    }

    /**
     * Terminate or cancel a tournament prematurely.
     */
    public function endTournament(Tournament $tournament): RedirectResponse
    {
        $tournament->update(['status' => Tournament::STATUS_COMPLETED]);

        return back()->with('success', "Le tournoi [{$tournament->title}] a été clôturé.");
    }

    /**
     * Delete tournament.
     */
    public function destroy(Tournament $tournament): RedirectResponse
    {
        $title = $tournament->title;
        $tournament->matches()->delete();
        $tournament->entries()->delete();
        $tournament->delete();

        return redirect()->route('admin.tournaments.index')
            ->with('success', "Le tournoi [{$title}] a été supprimé.");
    }

    /**
     * Generate 8-player bracket.
     */
    public function generateBracket(Tournament $tournament): RedirectResponse
    {
        try {
            $this->tournamentService->lockAndGenerateBracket($tournament);

            return back()->with('success', 'Tableau officiel généré avec succès ! Le tournoi est verrouillé.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Start and resolve all pending matches in tournament.
     */
    public function resolveAll(Tournament $tournament): RedirectResponse
    {
        try {
            $this->tournamentService->resolveAllPendingMatches($tournament);

            return back()->with('success', 'Tous les combats ont été résolus et les récompenses XP ont été distribuées !');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Helper to auto-fill tournament with AI test bots (up to 8).
     */
    public function fillBots(Tournament $tournament): RedirectResponse
    {
        $currentCount = $tournament->entries()->count();
        $needed = 8 - $currentCount;

        if ($needed <= 0) {
            return back()->with('info', 'Le tournoi est déjà complet (8 participants).');
        }

        for ($i = 1; $i <= $needed; $i++) {
            $botUsername = 'Bot_'.rand(100, 999).'_'.substr(md5(uniqid()), 0, 4);
            $botUser = User::create([
                'username' => $botUsername,
                'name' => 'IA '.$botUsername,
                'email' => $botUsername.'@arena.local',
                'password' => Hash::make('secret123'),
                'role' => User::ROLE_PLAYER,
            ]);

            $candidate = $this->fighterGenerator->generateCandidate();

            $botFighter = Fighter::create([
                'user_id' => $botUser->id,
                'name' => $candidate['name_suggestion'],
                'archetype' => $candidate['specialization'],
                'clan' => $candidate['clan'],
                'village' => $candidate['village'],
                'ninja_rank' => 'Genin',
                'primary_element' => $candidate['primary_element'],
                'specialization' => $candidate['specialization'],
                'stat_nin' => $candidate['stats']['stat_nin'],
                'stat_tai' => $candidate['stats']['stat_tai'],
                'stat_gen' => $candidate['stats']['stat_gen'],
                'stat_int' => $candidate['stats']['stat_int'],
                'stat_str' => $candidate['stats']['stat_str'],
                'stat_spd' => $candidate['stats']['stat_spd'],
                'stat_end' => $candidate['stats']['stat_end'],
                'stat_sea' => $candidate['stats']['stat_sea'],
                'power' => (int) round($candidate['stats']['stat_str'] * 4),
                'speed' => (int) round($candidate['stats']['stat_spd'] * 4),
                'defense' => (int) round($candidate['stats']['stat_end'] * 4),
                'control' => (int) round($candidate['stats']['stat_nin'] * 4),
                'special_power' => $candidate['primary_element'].' Jutsu',
                'talent' => $candidate['passive_description'],
                'portrait_path' => $candidate['portrait'],
                'level' => 1,
                'xp' => 0,
            ]);

            if (! empty($candidate['jutsu_ids'])) {
                $botFighter->jutsus()->sync($candidate['jutsu_ids']);
            }

            $this->tournamentService->registerFighter($tournament, $botFighter);
        }

        return back()->with('success', "{$needed} combattants IA ont été inscrits pour compléter le tournoi !");
    }
}
