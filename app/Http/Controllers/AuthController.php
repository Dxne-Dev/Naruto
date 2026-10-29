<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\IpTournamentQuota;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login submission.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $attempt = Auth::attempt([
            $loginField => $credentials['login'],
            'password' => $credentials['password'],
        ], (bool) ($credentials['remember'] ?? false));

        if ($attempt) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Redirection prioritaire pour l'administrateur
            if ($user && $user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Bienvenue dans le Poste de Commandement, Grand Maître.');
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Ravi de te revoir dans l\'arène !');
        }

        return back()
            ->withInput($request->only('login', 'remember'))
            ->withErrors(['login' => 'Identifiants invalides. Vérifie ton pseudo/email et mot de passe.']);
    }

    /**
     * Show registration form.
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle user registration.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        $user = User::create([
            'username' => $validated['username'],
            'name' => $validated['name'] ?? $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_PLAYER,
            'wheel_tokens' => 0,
            'registration_ip' => $ip,
            'device_fingerprint' => IpTournamentQuota::generateFingerprint($ip, $userAgent),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('onboarding.index')
            ->with('success', 'Bienvenue dans Otaku Arena ! Forgons ton shinobi pas à pas.');
    }

    /**
     * Log out the current user.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Tu as quitté l\'arène. À bientôt !');
    }
}
