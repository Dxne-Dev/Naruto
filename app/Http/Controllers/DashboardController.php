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
        // Si l'utilisateur est déjà connecté, aucun accueil : rediriger directement vers mon personnage (ou création si pas de perso)
        if ($request->user()) {
            if ($request->user()->fighter) {
                return redirect()->route('fighters.my-character');
            }

            return redirect()->route('onboarding.index');
        }

        // Visiteur non authentifié : Site public d'accueil fidèle au Panneau 1 de la maquette
        return view('dashboard');
    }
}
