<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    /**
     * Show admin profile edit form.
     */
    public function edit(): View
    {
        return view('admin.profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    /**
     * Update admin profile (name and password, but NOT email).
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

        return back()->with('success', 'Profil administrateur mis à jour avec succès. L\'adresse email reste inchangée.');
    }
}
