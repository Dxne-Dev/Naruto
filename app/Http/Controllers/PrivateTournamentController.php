<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Services\TournamentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PrivateTournamentController extends Controller
{
    public function __construct(
        protected TournamentService $tournamentService,
    ) {}

    /**
     * Show index of private tournaments (my rooms & form to create / join).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $fighter = $user?->fighter;

        $canAccess = $user?->canAccessPrivateTournament();

        // My private tournaments (as creator or participant)
        $myRooms = Tournament::where('is_private', true)
            ->where(function ($q) use ($user, $fighter) {
                $q->where('creator_id', $user->id);
                if ($fighter) {
                    $q->orWhereHas('entries', function ($eq) use ($fighter) {
                        $eq->where('fighter_id', $fighter->id);
                    });
                }
            })
            ->withCount('entries')
            ->with(['creator', 'entries.fighter'])
            ->latest()
            ->paginate(10);

        return view('tournaments.private.index', [
            'user' => $user,
            'fighter' => $fighter,
            'canAccess' => $canAccess,
            'myRooms' => $myRooms,
        ]);
    }

    /**
     * Store a newly created private tournament room.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $fighter = $user?->fighter;

        if (! $fighter) {
            return redirect()->route('fighters.create')
                ->with('error', 'Tu dois créer un combattant ninja pour ouvrir un salon de tournoi.');
        }

        if (! $user->canAccessPrivateTournament()) {
            return redirect()->route('payment.checkout')
                ->with('warning', 'Tu as déjà utilisé ton tournoi privé gratuit. Débloque le Pass Illimité pour créer et rejoindre des salons entre amis en illimité !');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:60'],
            'max_participants' => ['required', 'integer', 'in:4,8'],
        ]);

        // Generate unique readable room code (e.g. SHINOBI-742)
        do {
            $code = strtoupper(Str::random(3)).'-'.mt_rand(100, 999);
        } while (Tournament::where('room_code', $code)->exists());

        $tournament = Tournament::create([
            'title' => $validated['title'],
            'is_private' => true,
            'room_code' => $code,
            'creator_id' => $user->id,
            'max_participants' => (int) $validated['max_participants'],
            'status' => Tournament::STATUS_REGISTRATION,
            'registration_opens_at' => now(),
        ]);

        // Automatically register the creator's fighter
        $this->tournamentService->registerFighter(
            $tournament,
            $fighter,
            $request->ip(),
            null,
            $request->userAgent()
        );

        return redirect()->route('tournaments.private.lobby', $tournament)
            ->with('success', "Salon privé créé ! Partage le code [{$code}] à tes amis.");
    }

    /**
     * Join a private room via code or direct link.
     */
    public function join(Request $request): RedirectResponse
    {
        $user = $request->user();
        $fighter = $user?->fighter;

        if (! $fighter) {
            return redirect()->route('fighters.create')
                ->with('error', 'Tu dois forger un combattant shinobi avant de rejoindre un tournoi.');
        }

        $code = trim(strtoupper($request->input('room_code') ?? $request->query('code', '')));

        if (empty($code)) {
            return back()->with('error', 'Veuillez saisir un code de salon valide.');
        }

        $tournament = Tournament::where('is_private', true)
            ->where('room_code', $code)
            ->first();

        if (! $tournament) {
            return back()->with('error', "Aucun salon privé trouvé avec le code [{$code}].");
        }

        if ($tournament->entries()->where('fighter_id', $fighter->id)->exists()) {
            return redirect()->route('tournaments.private.lobby', $tournament);
        }

        if ($tournament->status !== Tournament::STATUS_REGISTRATION) {
            return back()->with('error', 'Les inscriptions à ce salon sont déjà fermées.');
        }

        if (! $user->canAccessPrivateTournament()) {
            return redirect()->route('payment.checkout')
                ->with('warning', 'Tu as déjà consommé ton unique tournoi privé gratuit. Débloque le Pass Illimité pour continuer à jouer avec tes amis !');
        }

        if ($tournament->entries()->count() >= $tournament->max_participants) {
            return back()->with('error', "Ce salon est complet ({$tournament->max_participants}/{$tournament->max_participants} combattants).");
        }

        try {
            $this->tournamentService->registerFighter(
                $tournament,
                $fighter,
                $request->ip(),
                null,
                $request->userAgent()
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tournaments.private.lobby', $tournament)
            ->with('success', "Tu as rejoint le salon privé [{$tournament->title}] !");
    }

    /**
     * Lobby waiting room for a private tournament.
     */
    public function lobby(Tournament $tournament): View|RedirectResponse
    {
        if (! $tournament->is_private) {
            return redirect()->route('tournaments.show', $tournament);
        }

        $tournament->load([
            'creator',
            'entries.fighter.user',
            'matches.fighterA',
            'matches.fighterB',
            'matches.winner',
        ]);

        $max = $tournament->max_participants ?? 8;
        $currentCount = $tournament->entries->count();
        $isFull = $currentCount >= $max;
        $isCreator = auth()->id() === $tournament->creator_id;

        return view('tournaments.private.lobby', [
            'tournament' => $tournament,
            'max' => $max,
            'currentCount' => $currentCount,
            'isFull' => $isFull,
            'isCreator' => $isCreator,
        ]);
    }

    /**
     * Start the private tournament (creator only or auto once full).
     */
    public function start(Request $request, Tournament $tournament): RedirectResponse
    {
        if (! $tournament->is_private) {
            return redirect()->route('tournaments.show', $tournament);
        }

        $user = $request->user();
        if ($tournament->creator_id !== $user->id) {
            return back()->with('error', 'Seul le créateur du salon peut lancer le tournoi.');
        }

        $count = $tournament->entries()->count();
        $target = $tournament->max_participants ?? 8;

        if ($count < $target) {
            return back()->with('error', "Il manque encore des participants ({$count}/{$target}). Invite tes amis !");
        }

        try {
            $this->tournamentService->startTournament($tournament);
            $this->tournamentService->resolveAllPendingMatches($tournament);

            return redirect()->route('tournaments.private.lobby', $tournament)
                ->with('success', 'Le Tournoi Privé a été simulé avec succès ! Les combats et les récompenses sont distribués.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Erreur lors du lancement : '.$e->getMessage());
        }
    }
}
