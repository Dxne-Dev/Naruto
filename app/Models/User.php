<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'username',
    'name',
    'email',
    'password',
    'role',
    'wheel_tokens',
    'has_paid_microtransaction',
    'registration_ip',
    'device_fingerprint',
    'is_suspended',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_PLAYER = 'player';

    public const ROLE_ADMIN = 'admin';

    /**
     * Backward compatibility alias.
     */
    public const ROLE_GAME_MASTER = self::ROLE_ADMIN;

    /**
     * Check if the user is currently online based on active session (last 5 minutes).
     */
    public function isOnline(): bool
    {
        $threshold = now()->subMinutes(5)->timestamp;

        return DB::table('sessions')
            ->where('user_id', $this->id)
            ->where('last_activity', '>=', $threshold)
            ->exists();
    }

    /**
     * Check if the user is an Admin / Game Master.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN || $this->role === 'game_master';
    }

    /**
     * Check if the user is a Game Master (alias for isAdmin).
     */
    public function isGameMaster(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Active fighter relationship.
     */
    public function fighter(): HasOne
    {
        return $this->hasOne(Fighter::class);
    }

    /**
     * Friendships initiated by the user.
     */
    public function sentFriendships()
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    /**
     * Friendships received by the user.
     */
    public function receivedFriendships()
    {
        return $this->hasMany(Friendship::class, 'friend_id');
    }

    /**
     * Retrieve all accepted friends for this user.
     */
    public function friends()
    {
        $sentFriendIds = $this->sentFriendships()
            ->where('status', Friendship::STATUS_ACCEPTED)
            ->pluck('friend_id');

        $receivedFriendIds = $this->receivedFriendships()
            ->where('status', Friendship::STATUS_ACCEPTED)
            ->pluck('user_id');

        $allFriendIds = $sentFriendIds->merge($receivedFriendIds)->unique();

        return User::whereIn('id', $allFriendIds)->with('fighter');
    }

    /**
     * Check if this user is confirmed friends with another user.
     */
    public function isFriendsWith(int|User $user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        if ($this->id === $userId) {
            return false;
        }

        return Friendship::where(function ($q) use ($userId) {
            $q->where(function ($pair) use ($userId) {
                $pair->where('user_id', $this->id)->where('friend_id', $userId);
            })->orWhere(function ($pair) use ($userId) {
                $pair->where('user_id', $userId)->where('friend_id', $this->id);
            });
        })->where('status', Friendship::STATUS_ACCEPTED)->exists();
    }

    /**
     * Get pending friendship requests received.
     */
    public function pendingFriendRequestsReceived()
    {
        return $this->receivedFriendships()
            ->where('status', Friendship::STATUS_PENDING)
            ->with(['user.fighter']);
    }

    /**
     * Get pending friendship requests sent.
     */
    public function pendingFriendRequestsSent()
    {
        return $this->sentFriendships()
            ->where('status', Friendship::STATUS_PENDING)
            ->with(['friend.fighter']);
    }

    /**
     * Check if user can enter more tournaments (quota of 2 if free).
     */
    public function canEnterTournament(?string $ip = null, ?string $userAgent = null): bool
    {
        if ($this->has_paid_microtransaction) {
            return true;
        }

        // Count participations by user
        $userEntryCount = TournamentEntry::whereHas('fighter', function ($query) {
            $query->where('user_id', $this->id);
        })->count();

        if ($userEntryCount >= 2) {
            return false;
        }

        // Check persistent device fingerprint or IP quota
        if ($ip || $userAgent) {
            if (IpTournamentQuota::hasReachedLimit($ip, $userAgent)) {
                return false;
            }

            $fingerprint = IpTournamentQuota::generateFingerprint($ip, $userAgent);
            $fingerprintEntryCount = TournamentEntry::where('device_fingerprint', $fingerprint)->count();
            if ($fingerprintEntryCount >= 2) {
                return false;
            }

            if ($ip) {
                $legacyIpEntryCount = TournamentEntry::where('registration_ip', $ip)
                    ->whereNull('device_fingerprint')
                    ->count();
                if ($legacyIpEntryCount >= 2) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if user can enter or create a private tournament room (quota of 1 if free pass).
     * With Pass paid, access is unlimited.
     */
    public function canAccessPrivateTournament(): bool
    {
        if ($this->has_paid_microtransaction) {
            return true;
        }

        // Check if user has already participated in or created a private tournament
        $privateEntriesCount = TournamentEntry::whereHas('fighter', function ($query) {
            $query->where('user_id', $this->id);
        })->whereHas('tournament', function ($query) {
            $query->where('is_private', true);
        })->count();

        return $privateEntriesCount < 1;
    }

    public function passRequests()
    {
        return $this->hasMany(PassRequest::class);
    }

    public function latestPassRequest()
    {
        return $this->hasOne(PassRequest::class)->latestOfMany();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'wheel_tokens' => 'integer',
            'has_paid_microtransaction' => 'boolean',
            'is_suspended' => 'boolean',
        ];
    }
}
