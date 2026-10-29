@extends('layouts.app')

@section('title', 'Aucun Combattant Assigné — Otaku Arena')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-10 px-4">
    <div class="relative w-full max-w-xl text-center">
        <!-- Glowing aura in background -->
        <div class="pointer-events-none absolute -top-16 left-1/2 -translate-x-1/2 w-80 h-80 rounded-full bg-shinobi-red/15 blur-3xl"></div>

        <div class="relative z-10 rounded-3xl border border-shinobi-border bg-gradient-to-b from-[#1C1822] via-shinobi-card to-[#120F15] p-8 sm:p-12 shadow-2xl backdrop-blur-xl">
            <!-- Emblème Icone Shonen -->
            <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-6 rounded-3xl bg-gradient-to-br from-shinobi-red/25 to-rose-950/40 border border-shinobi-red/50 flex items-center justify-center text-shinobi-red shadow-xl shadow-shinobi-red/25">
                <i class="bi bi-person-x-fill text-4xl sm:text-5xl"></i>
            </div>

            <!-- Badge statut -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-shinobi-red/10 border border-shinobi-red/30 font-mono text-[11px] font-bold tracking-widest text-shinobi-red uppercase mb-4">
                <span class="w-2 h-2 rounded-full bg-shinobi-red animate-ping"></span>
                REGISTRE DE L'ARÈNE VIDE
            </div>

            <h1 class="font-serif font-black text-2xl sm:text-4xl text-white uppercase tracking-tight mb-3">
                Aucun Shinobi Éveillé
            </h1>

            <p class="font-sans text-xs sm:text-sm text-slate-300 leading-relaxed max-w-md mx-auto mb-8 font-medium">
                Pour rejoindre les tournois de l'Examen Chūnin, affronter d'autres ninjas et grimper le classement de l'arène, forge d'abord ton combattant original à travers la Roue du Destin.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('onboarding.index') }}" 
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white font-mono text-xs sm:text-sm font-bold uppercase tracking-wider shadow-lg shadow-shinobi-red/30 transition transform hover:scale-[1.02] active:scale-[0.98]">
                    <i class="bi bi-fire text-base"></i>
                    <span>FORGER MON SHINOBI DANS L'ARÈNE ▸</span>
                </a>
                <a href="{{ route('tournaments.open-list') }}" 
                   class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-3.5 rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-300 font-mono text-xs uppercase tracking-wider transition">
                    Voir les tournois
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
