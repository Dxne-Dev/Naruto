<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Friendship;
use App\Models\MatchGame;
use App\Models\User;
use App\Services\BattleEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FriendController extends Controller
{
    public function __construct(
        protected BattleEngine $battleEngine
    ) {}

    /**
     * Display friends list, pending requests, challenges, and friend activities.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // 1. Friends list (accepted)
        $friends = $user->friends()->get();
        $friendIds = $friends->pluck('id');
        $friendFighterIds = $friends->pluck('fighter.id')->filter()->values();

        // 2. Pending requests received
        $pendingReceived = $user->pendingFriendRequestsReceived()->get();

        // 3. Pending requests sent
        $pendingSent = $user->pendingFriendRequestsSent()->get();

        // 4. Challenges received (pending)
        $pendingChallenges = Challenge::where('receiver_id', $user->id)
            ->where('status', Challenge::STATUS_PENDING)
            ->with(['sender.fighter', 'senderFighter'])
            ->latest()
            ->get();

        // 5. Challenges sent (pending)
        $sentChallenges = Challenge::where('sender_id', $user->id)
            ->where('status', Challenge::STATUS_PENDING)
            ->with(['receiver.fighter', 'receiverFighter'])
            ->latest()
            ->get();

        // 6. Real activities of confirmed friends only
        $recentActivities = collect();
        if ($friendFighterIds->isNotEmpty()) {
            // A. Tournament victories of friends
            $tournamentWins = MatchGame::whereIn('winner_id', $friendFighterIds)
                ->where('round', 3) // Finale gagnée = vainqueur du tournoi
                ->where('status', MatchGame::STATUS_COMPLETED)
                ->with(['winner.user', 'tournament'])
                ->latest('completed_at')
                ->take(5)
                ->get()
                ->map(function ($match) {
                    return [
                        'type' => 'tournament_win',
                        'icon' => '🏆',
                        'icon_bg' => 'bg-shinobi-gold/15 text-shinobi-gold',
                        'fighter_name' => $match->winner?->name ?? 'Shinobi',
                        'description' => 'a remporté le tournoi '.($match->tournament?->title ?? 'Chūnin'),
                        'time' => $match->completed_at ?? $match->created_at,
                    ];
                });

            // B. Friendly or training battles played by friends
            $recentBattles = Battle::where(function ($q) use ($friendFighterIds) {
                $q->whereIn('fighter_a_id', $friendFighterIds)
                    ->orWhereIn('fighter_b_id', $friendFighterIds);
            })
                ->whereNull('match_id') // Duels hors tournoi
                ->with(['fighterA.user', 'fighterB.user', 'winner'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($battle) use ($friendFighterIds) {
                    $friendFighter = $friendFighterIds->contains($battle->fighter_a_id) ? $battle->fighterA : $battle->fighterB;
                    $opponent = $friendFighter->id === $battle->fighter_a_id ? $battle->fighterB : $battle->fighterA;
                    $won = $battle->winner_id === $friendFighter->id;

                    return [
                        'type' => 'battle',
                        'icon' => '⚔️',
                        'icon_bg' => $won ? 'bg-emerald-500/15 text-emerald-400' : 'bg-shinobi-red/15 text-shinobi-red',
                        'fighter_name' => $friendFighter?->name ?? 'Shinobi',
                        'description' => ($won ? 'a remporté un duel amical' : 'a disputé un duel acharné').($opponent ? " face à {$opponent->name}" : ''),
                        'time' => $battle->created_at,
                    ];
                });

            $recentActivities = $tournamentWins->concat($recentBattles)
                ->sortByDesc('time')
                ->take(6)
                ->values();
        }

        // 7. Search results if query provided
        $search = $request->query('q');
        $searchResults = collect();

        if ($search && strlen(trim($search)) >= 2) {
            $searchResults = User::where('id', '!=', $user->id)
                ->where(function ($query) use ($search) {
                    $query->where('username', 'LIKE', "%{$search}%")
                        ->orWhere('name', 'LIKE', "%{$search}%")
                        ->orWhereHas('fighter', function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%{$search}%");
                        });
                })
                ->with('fighter')
                ->limit(10)
                ->get();
        }

        // Calculate actual online status for each friend via session last_activity
        $fiveMinutesAgo = now()->subMinutes(5)->timestamp;
        $onlineUserIds = DB::table('sessions')
            ->whereIn('user_id', $friendIds)
            ->where('last_activity', '>=', $fiveMinutesAgo)
            ->pluck('user_id')
            ->flip()
            ->all();

        return view('friends.index', [
            'user' => $user,
            'friends' => $friends,
            'onlineUserIds' => $onlineUserIds,
            'pendingReceived' => $pendingReceived,
            'pendingSent' => $pendingSent,
            'pendingChallenges' => $pendingChallenges,
            'sentChallenges' => $sentChallenges,
            'recentActivities' => $recentActivities,
            'searchResults' => $searchResults,
            'search' => $search,
        ]);
    }

    /**
     * Send a friend request.
     */
    public function sendRequest(Request $request): RedirectResponse
    {
        $request->validate([
            'friend_id' => 'nullable|exists:users,id',
            'friend_username' => 'nullable|string',
        ]);

        $user = $request->user();
        $targetUser = null;

        if ($request->filled('friend_id')) {
            $targetUser = User::find($request->input('friend_id'));
        } elseif ($request->filled('friend_username')) {
            $targetUser = User::where('username', $request->input('friend_username'))->first();
        }

        if (! $targetUser) {
            return back()->with('error', 'Shinobi introuvable.');
        }

        if ($user->id === $targetUser->id) {
            return back()->with('error', 'Tu ne peux pas t\'ajouter toi-même en ami !');
        }

        // Check if a friendship already exists between them in any direction
        $existing = Friendship::where(function ($q) use ($user, $targetUser) {
            $q->where('user_id', $user->id)->where('friend_id', $targetUser->id);
        })->orWhere(function ($q) use ($user, $targetUser) {
            $q->where('user_id', $targetUser->id)->where('friend_id', $user->id);
        })->first();

        if ($existing) {
            if ($existing->status === Friendship::STATUS_ACCEPTED) {
                return back()->with('info', "Tu es déjà ami avec {$targetUser->username}.");
            }

            if ($existing->status === Friendship::STATUS_PENDING) {
                if ($existing->user_id === $targetUser->id && $existing->friend_id === $user->id) {
                    $existing->update(['status' => Friendship::STATUS_ACCEPTED]);

                    return back()->with('success', "Demande mutuelle ! {$targetUser->username} est désormais ton ami.");
                }

                return back()->with('info', 'Une demande d\'ami est déjà en attente.');
            }

            if ($existing->status === Friendship::STATUS_DECLINED) {
                $existing->update([
                    'user_id' => $user->id,
                    'friend_id' => $targetUser->id,
                    'status' => Friendship::STATUS_PENDING,
                ]);

                return back()->with('success', "Demande d'ami renvoyée à {$targetUser->username}.");
            }
        }

        Friendship::create([
            'user_id' => $user->id,
            'friend_id' => $targetUser->id,
            'status' => Friendship::STATUS_PENDING,
        ]);

        return back()->with('success', "Demande d'ami envoyée à {$targetUser->username} !");
    }

    /**
     * Accept a pending friend request.
     */
    public function accept(Friendship $friendship, Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($friendship->friend_id !== $user->id) {
            return back()->with('error', 'Action non autorisée.');
        }

        $friendship->update(['status' => Friendship::STATUS_ACCEPTED]);

        return back()->with('success', "Tu es maintenant ami avec {$friendship->user->username} !");
    }

    /**
     * Decline a pending friend request.
     */
    public function decline(Friendship $friendship, Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($friendship->friend_id !== $user->id) {
            return back()->with('error', 'Action non autorisée.');
        }

        $friendship->update(['status' => Friendship::STATUS_DECLINED]);

        return back()->with('info', "Demande d'ami refusée.");
    }

    /**
     * Remove / unfriend.
     */
    public function destroy(User $friend, Request $request): RedirectResponse
    {
        $user = $request->user();

        Friendship::where(function ($q) use ($user, $friend) {
            $q->where('user_id', $user->id)->where('friend_id', $friend->id);
        })->orWhere(function ($q) use ($user, $friend) {
            $q->where('user_id', $friend->id)->where('friend_id', $user->id);
        })->delete();

        return back()->with('info', "{$friend->username} a été retiré de ta liste d'amis.");
    }

    /**
     * Send a direct duel challenge to a friend.
     */
    public function challenge(User $friend, Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isFriendsWith($friend)) {
            return back()->with('error', 'Vous devez être amis pour vous défier en duel privé.');
        }

        $fighterA = $user->fighter;
        $fighterB = $friend->fighter;

        if (! $fighterA) {
            return redirect()->route('onboarding.index')
                ->with('error', 'Tu dois créer ton Shinobi avant de pouvoir défier un ami.');
        }

        if (! $fighterB) {
            return back()->with('error', "{$friend->username} n'a pas encore créé son Shinobi.");
        }

        // Check if there is already an active pending challenge between them
        $existing = Challenge::where('sender_id', $user->id)
            ->where('receiver_id', $friend->id)
            ->where('status', Challenge::STATUS_PENDING)
            ->first();

        if ($existing) {
            return back()->with('info', "Un défi est déjà en attente auprès de {$friend->username}.");
        }

        Challenge::create([
            'sender_id' => $user->id,
            'receiver_id' => $friend->id,
            'sender_fighter_id' => $fighterA->id,
            'receiver_fighter_id' => $fighterB->id,
            'status' => Challenge::STATUS_PENDING,
        ]);

        return back()->with('success', "⚔️ Défi envoyé avec succès à {$friend->username} ! Il apparaîtra dans ses défis reçus.");
    }

    /**
     * Accept a received challenge and execute the battle.
     */
    public function acceptChallenge(Challenge $challenge, Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($challenge->receiver_id !== $user->id) {
            return back()->with('error', 'Action non autorisée.');
        }

        if ($challenge->status !== Challenge::STATUS_PENDING) {
            return back()->with('info', 'Ce défi n\'est plus disponible.');
        }

        $fighterA = $challenge->senderFighter;
        $fighterB = $challenge->receiverFighter;

        if (! $fighterA || ! $fighterB) {
            return back()->with('error', 'Un des shinobis du duel n\'est plus disponible.');
        }

        // Simulate battle
        $battle = $this->battleEngine->simulate($fighterA, $fighterB);

        $challenge->update([
            'status' => Challenge::STATUS_ACCEPTED,
            'battle_id' => $battle->id,
            'responded_at' => now(),
        ]);

        return redirect()->route('battles.show', $battle)
            ->with('success', "⚔️ Défi accepté ! Le duel contre {$challenge->sender->username} a eu lieu !");
    }

    /**
     * Decline a received challenge.
     */
    public function declineChallenge(Challenge $challenge, Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($challenge->receiver_id !== $user->id) {
            return back()->with('error', 'Action non autorisée.');
        }

        if ($challenge->status !== Challenge::STATUS_PENDING) {
            return back()->with('info', 'Ce défi n\'est plus en attente.');
        }

        $challenge->update([
            'status' => Challenge::STATUS_DECLINED,
            'responded_at' => now(),
        ]);

        return back()->with('info', 'Le défi a été décliné.');
    }
}
