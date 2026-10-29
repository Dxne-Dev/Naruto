<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\XpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminGiftController extends Controller
{
    public function __construct(
        protected XpService $xpService,
    ) {}

    /**
     * Display the Admin Gift interface with real players.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');

        $usersQuery = User::query()
            ->where('role', '!=', User::ROLE_ADMIN)
            ->with('fighter');

        if ($search) {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('fighter', function ($fq) use ($search) {
                        $fq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $users = $usersQuery->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total_real_players' => User::where('role', '!=', User::ROLE_ADMIN)->count(),
            'total_pass_holders' => User::where('role', '!=', User::ROLE_ADMIN)->where('has_paid_microtransaction', true)->count(),
            'total_tokens_circulating' => User::where('role', '!=', User::ROLE_ADMIN)->sum('wheel_tokens'),
        ];

        return view('admin.gifts.index', [
            'users' => $users,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    /**
     * Toggle Pass Shinobi Illimité on/off for a player.
     */
    public function togglePass(User $user): RedirectResponse
    {
        $newStatus = ! $user->has_paid_microtransaction;
        $user->update([
            'has_paid_microtransaction' => $newStatus,
        ]);

        if ($user->fighter) {
            // Si on active le pass et que le portrait n'est pas encore prêt, on le place en pending
            if ($newStatus && $user->fighter->portrait_status === 'none') {
                $user->fighter->update([
                    'portrait_status' => 'pending',
                    'portrait_ordered_at' => now(),
                ]);
            }
        }

        $label = $newStatus ? 'activé' : 'désactivé';

        return back()->with('success', "Le Pass Shinobi Illimité a été {$label} avec succès pour {$user->username}.");
    }

    /**
     * Send XP gift to player's fighter.
     */
    public function sendXp(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:50000'],
            'reason' => ['nullable', 'string', 'max:150'],
        ]);

        if (! $user->fighter) {
            return back()->with('error', "Impossible d'envoyer de l'XP : {$user->username} n'a pas encore créé de Shinobi.");
        }

        $amount = (int) $validated['amount'];
        $reason = $validated['reason'] ?? 'Cadeau de l\'Administration Grand Maître';

        $this->xpService->awardXp(
            fighter: $user->fighter,
            amount: $amount,
            reason: $reason
        );

        return back()->with('success', "+{$amount} XP injectés au Shinobi {$user->fighter->name} ({$user->username}).");
    }

    /**
     * Send or adjust Wheel Tokens to a player.
     */
    public function sendTokens(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:-1000', 'max:1000', 'not_in:0'],
        ]);

        $amount = (int) $validated['amount'];
        $newTokens = max(0, $user->wheel_tokens + $amount);

        $user->update([
            'wheel_tokens' => $newTokens,
        ]);

        $sign = $amount > 0 ? "+{$amount}" : "{$amount}";

        return back()->with('success', "{$sign} Jeton(s) appliqués à {$user->username} (Nouveau solde : {$newTokens} jetons).");
    }
}
