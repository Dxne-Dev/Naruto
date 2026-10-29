<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main landing page or redirect authenticated users directly to their character.
     */
    public function index(Request $request): View|RedirectResponse
    {
        // Si l'utilisateur est déjà connecté
        if ($user = $request->user()) {
            // L'administrateur est immédiatement dirigé vers le cockpit admin
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->fighter) {
                return redirect()->route('fighters.my-character');
            }

            return redirect()->route('onboarding.index');
        }

        // Visiteur non authentifié : Site public d'accueil fidèle au Panneau 1 de la maquette
        return view('dashboard');
    }
}
