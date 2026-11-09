<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserAdminController extends Controller
{
    /**
     * List all players and their characters with real players/bots filtering and search.
     */
    public function index(Request $request): View
    {
        $type = $request->query('type', 'real'); // 'real' (default), 'bots', 'all'
        $search = trim((string) $request->query('q', ''));

        // Badge counts for tabs
        $realCount = User::realPlayers()->count();
        $botsCount = User::bots()->count();
        $totalCount = User::where('role', User::ROLE_PLAYER)->count();

        // Scope based on selected tab
        $query = match ($type) {
            'bots' => User::with('fighter')->bots(),
            'all' => User::with('fighter')->where('role', User::ROLE_PLAYER),
            default => User::with('fighter')->realPlayers(),
        };

        // Keyword search across username, name, email and shinobi fighter name/clan
        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('username', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('fighter', function ($fq) use ($search) {
                        $fq->where('name', 'like', "%{$search}%")
                            ->orWhere('clan', 'like', "%{$search}%");
                    });
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'type' => $type,
            'search' => $search,
            'realCount' => $realCount,
            'botsCount' => $botsCount,
            'totalCount' => $totalCount,
        ]);
    }

    /**
     * Purge all injected bot accounts and their fighters.
     */
    public function purgeBots(): RedirectResponse
    {
        $bots = User::bots()->with('fighter')->get();
        $count = $bots->count();

        foreach ($bots as $bot) {
            if ($bot->fighter) {
                $bot->fighter->delete();
            }
            $bot->delete();
        }

        return redirect()->route('admin.users.index', ['type' => 'real'])
            ->with('success', "{$count} bot(s) injecté(s) ont été purgés avec succès.");
    }

    /**
     * Suspend or unsuspend user account.
     */
    public function toggleSuspension(User $user): RedirectResponse
    {
        $user->update([
            'is_suspended' => ! $user->is_suspended,
        ]);

        $status = $user->is_suspended ? 'suspendu' : 'réactivé';

        return back()->with('success', "Le compte de {$user->username} a été {$status} avec succès.");
    }

    /**
     * Delete user and its character.
     */
    public function destroy(User $user): RedirectResponse
    {
        $username = $user->username;

        if ($user->fighter) {
            $user->fighter->delete();
        }

        $user->delete();

        return back()->with('success', "Le compte de {$username} et son personnage ont été supprimés.");
    }

    /**
     * Reset or delete only the character of a user.
     */
    public function deleteFighter(User $user): RedirectResponse
    {
        if ($user->fighter) {
            $user->fighter->delete();

            return back()->with('success', "Le personnage de {$user->username} a été révoqué.");
        }

        return back()->with('info', "Cet utilisateur n'a aucun personnage actif.");
    }
}
