<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserAdminController extends Controller
{
    /**
     * List all players and their characters.
     */
    public function index(): View
    {
        $users = User::with('fighter')
            ->where('role', User::ROLE_PLAYER)
            ->latest()
            ->paginate(15);

        return view('admin.users.index', [
            'users' => $users,
        ]);
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
