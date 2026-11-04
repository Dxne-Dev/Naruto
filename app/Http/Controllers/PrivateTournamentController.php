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
            ->with(['creator.fighter', 'entries.fighter'])
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
     * Accessible by guests (public invitation view with OpenGraph) and authenticated users.
     */
    public function join(Request $request): View|RedirectResponse
    {
        $code = trim(strtoupper($request->input('room_code') ?? $request->query('code', '')));

        if (empty($code)) {
            if (auth()->check()) {
                return redirect()->route('tournaments.private.index')
                    ->with('error', 'Veuillez saisir un code de salon valide.');
            }

            return redirect()->route('dashboard');
        }

        $tournament = Tournament::where('is_private', true)
            ->where('room_code', $code)
            ->with(['creator.fighter', 'entries.fighter.user'])
            ->first();

        if (! $tournament) {
            if (auth()->check()) {
                return redirect()->route('tournaments.private.index')
                    ->with('error', "Aucun salon privé trouvé avec le code [{$code}].");
            }

            return redirect()->route('dashboard')
                ->with('error', 'Ce salon privé est introuvable ou a été supprimé.');
        }

        $creatorFighter = $tournament->creator?->fighter;
        $creatorShinobiName = $creatorFighter?->name ?? $tournament->creator?->name ?? 'Un Shinobi';
        $challengeTitle = "{$creatorShinobiName} vous défie pour le tournoi d'examen chunin !";
        $ogImage = $creatorFighter?->display_avatar_url ?? asset('images/naruto_hero_art.png');
        $shareUrl = route('tournaments.private.join', ['code' => $tournament->room_code]);

        // If user is a guest (or crawler/bot preview), display the public invitation card with OpenGraph metadata
        if (! auth()->check()) {
            $request->session()->put('pending_tournament_code', $tournament->room_code);

            return view('tournaments.private.invite', [
                'tournament' => $tournament,
                'creatorFighter' => $creatorFighter,
                'creatorShinobiName' => $creatorShinobiName,
                'challengeTitle' => $challengeTitle,
                'ogImage' => $ogImage,
                'shareUrl' => $shareUrl,
            ]);
        }

        $user = $request->user();
        $fighter = $user?->fighter;

        if (! $fighter) {
            $request->session()->put('pending_tournament_code', $tournament->room_code);

            return redirect()->route('fighters.create')
                ->with('info', "Tu as été défié par {$creatorShinobiName} pour le tournoi d'examen chunin ! Forge ton combattant shinobi pour entrer dans l'arène.");
        }

        if ($tournament->entries()->where('fighter_id', $fighter->id)->exists()) {
            return redirect()->route('tournaments.private.lobby', $tournament);
        }

        if ($tournament->status !== Tournament::STATUS_REGISTRATION) {
            return redirect()->route('tournaments.private.lobby', $tournament)
                ->with('error', 'Les inscriptions à ce salon sont déjà fermées.');
        }

        if (! $user->canAccessPrivateTournament()) {
            return redirect()->route('payment.checkout')
                ->with('warning', 'Tu as déjà consommé ton unique tournoi privé gratuit. Débloque le Pass Illimité pour continuer à jouer avec tes amis !');
        }

        if ($tournament->entries()->count() >= $tournament->max_participants) {
            return redirect()->route('tournaments.private.lobby', $tournament)
                ->with('error', "Ce salon est complet ({$tournament->max_participants}/{$tournament->max_participants} combattants).");
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
            return redirect()->route('tournaments.private.index')->with('error', $e->getMessage());
        }

        return redirect()->route('tournaments.private.lobby', $tournament)
            ->with('success', "Tu as rejoint le salon privé [{$tournament->title}] pour relever le défi de {$creatorShinobiName} !");
    }

    /**
     * Direct invitation URL alias by code.
     */
    public function invitation(Request $request, string $code): View|RedirectResponse
    {
        $request->merge(['code' => $code]);

        return $this->join($request);
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
            'creator.fighter',
            'entries.fighter.user',
            'matches.fighterA',
            'matches.fighterB',
            'matches.winner',
        ]);

        $max = $tournament->max_participants ?? 8;
        $currentCount = $tournament->entries->count();
        $isFull = $currentCount >= $max;
        $isCreator = auth()->id() === $tournament->creator_id;

        $creatorFighter = $tournament->creator?->fighter;
        $creatorShinobiName = $creatorFighter?->name ?? $tournament->creator?->name ?? 'Un Shinobi';
        $challengeTitle = "{$creatorShinobiName} vous défie pour le tournoi d'examen chunin !";
        $ogImage = $creatorFighter?->display_avatar_url ?? asset('images/naruto_hero_art.png');
        $shareUrl = route('tournaments.private.join', ['code' => $tournament->room_code]);

        return view('tournaments.private.lobby', [
            'tournament' => $tournament,
            'max' => $max,
            'currentCount' => $currentCount,
            'isFull' => $isFull,
            'isCreator' => $isCreator,
            'creatorFighter' => $creatorFighter,
            'creatorShinobiName' => $creatorShinobiName,
            'challengeTitle' => $challengeTitle,
            'ogImage' => $ogImage,
            'shareUrl' => $shareUrl,
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
