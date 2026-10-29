@extends('layouts.app')

@section('title', 'Aucune Stratégie Configurable — Otaku Arena')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-10 px-4">
    <div class="relative w-full max-w-xl text-center">
        <!-- Glowing aura in background -->
        <div class="pointer-events-none absolute -top-16 left-1/2 -translate-x-1/2 w-80 h-80 rounded-full bg-shinobi-chakra/15 blur-3xl"></div>

        <div class="relative z-10 rounded-3xl border border-shinobi-border bg-gradient-to-b from-[#161824] via-shinobi-card to-[#0F1118] p-8 sm:p-12 shadow-2xl backdrop-blur-xl">
            <!-- Emblème Icone Tactique -->
            <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-6 rounded-3xl bg-gradient-to-br from-shinobi-chakra/25 to-cyan-950/40 border border-shinobi-chakra/50 flex items-center justify-center text-shinobi-chakra shadow-xl shadow-shinobi-chakra/25">
                <i class="bi bi-sliders text-4xl sm:text-5xl"></i>
            </div>

            <!-- Badge statut -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-shinobi-chakra/10 border border-shinobi-chakra/30 font-mono text-[11px] font-bold tracking-widest text-shinobi-chakra uppercase mb-4">
                <span class="w-2 h-2 rounded-full bg-shinobi-chakra animate-pulse"></span>
                TACTIQUES VERROUILLÉES
            </div>

            <h1 class="font-serif font-black text-2xl sm:text-4xl text-white uppercase tracking-tight mb-3">
                Aucun Shinobi à Commander
            </h1>

            <p class="font-sans text-xs sm:text-sm text-slate-300 leading-relaxed max-w-md mx-auto mb-8 font-medium">
                Les postures de combat (Offensive, Équilibrée, Défensive) et l'algorithme d'utilisation des Jutsus nécessitent un combattant actif lié à ton compte.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('onboarding.index') }}" 
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white font-mono text-xs sm:text-sm font-bold uppercase tracking-wider shadow-lg shadow-shinobi-red/30 transition transform hover:scale-[1.02] active:scale-[0.98]">
                    <i class="bi bi-person-plus-fill text-base"></i>
                    <span>ÉVEILLER MON SHINOBI EN PREMIER ▸</span>
                </a>
                <a href="{{ route('leaderboard.index') }}" 
                   class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-3.5 rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-300 font-mono text-xs uppercase tracking-wider transition">
                    Consulter le Rang
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
