<?php

namespace App\Http\Controllers;

use App\Models\IpTournamentQuota;
use App\Models\TournamentEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show player profile edit form.
     */
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    /**
     * Update player profile (name, username, password, but NOT email).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username,'.$user->id],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ]);

        $dataToUpdate = [
            'name' => $validated['name'],
            'username' => $validated['username'],
        ];

        if (! empty($validated['password'])) {
            $dataToUpdate['password'] = Hash::make($validated['password']);
        }

        $user->update($dataToUpdate);

        return back()->with('success', 'Profil ninja mis à jour avec succès. L\'email lié au personnage est immuable.');
    }

    /**
     * Delete user account permanently.
     * Retains persistent IP quota if microtransaction was not paid.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $request->validate([
            'delete_confirmation_password' => ['required', 'current_password'],
        ], [
            'delete_confirmation_password.required' => 'Le mot de passe actuel est requis pour confirmer la suppression.',
            'delete_confirmation_password.current_password' => 'Le mot de passe saisi est incorrect.',
        ]);

        $clientIp = $request->ip();
        $userAgent = $request->userAgent();
        $fingerprint = IpTournamentQuota::generateFingerprint($clientIp, $userAgent);

        // If user did not pay microtransaction, ensure their tournament usage is locked on their device fingerprint & IP
        if (! $user->has_paid_microtransaction) {
            $userTournamentsCount = TournamentEntry::whereHas('fighter', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count();

            if ($userTournamentsCount > 0 && ($clientIp || $userAgent)) {
                $quota = IpTournamentQuota::firstOrCreate(
                    ['device_fingerprint' => $fingerprint],
                    [
                        'ip_address' => $clientIp,
                        'user_agent' => $userAgent,
                        'free_participations_count' => 0,
                    ]
                );
                // Ensure at least the count of user entries is recorded
                if ($quota->free_participations_count < $userTournamentsCount) {
                    $quota->update([
                        'free_participations_count' => min(2, $quota->free_participations_count + $userTournamentsCount),
                        'ip_address' => $clientIp ?? $quota->ip_address,
                        'user_agent' => $userAgent ?? $quota->user_agent,
                    ]);
                }
            }
        }

        // Delete fighter if exists
        if ($user->fighter) {
            $user->fighter->delete();
        }

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect()->route('dashboard')
            ->with('info', 'Ton compte et ton personnage ont été définitivement supprimés.');
    }
}
