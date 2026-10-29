@extends('layouts.app', ['title' => '500 — Erreur Interne du Serveur | Otaku Arena', 'isErrorPage' => true])

@section('content')
<div class="relative w-full min-h-[calc(100vh-4rem)] flex items-center bg-[#0E0D0F] overflow-hidden -mt-0">
    {{-- Background Artwork 16:9 cinématographique avec Kakashi à droite --}}
    <div class="absolute inset-0 z-0 pointer-events-none">
        <img src="{{ asset('images/errors/500-kakashi.jpg') }}" 
             alt="Kakashi Hatake - Erreur Interne du Serveur" 
             class="w-full h-full object-cover object-center filter brightness-95">
        {{-- Dégradés cinématographiques doux pour fondre le texte à gauche sans masquer Kakashi --}}
        <div class="absolute inset-0 bg-gradient-to-r from-[#0E0D0F]/95 via-[#0E0D0F]/70 to-transparent w-full md:w-3/5"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#0E0D0F] via-transparent to-[#0E0D0F]/40"></div>
    </div>

    {{-- Contenu aligné à gauche --}}
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20 w-full">
        <div class="max-w-xl lg:max-w-2xl">
            {{-- Badge d'alerte --}}
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#38E2F6]/15 border border-[#38E2F6]/40 text-[#38E2F6] text-xs font-mono font-bold tracking-widest uppercase mb-3">
                <span class="w-2 h-2 rounded-full bg-[#38E2F6] animate-pulse"></span>
                SYSTEM OVERLOAD
            </div>

            {{-- Code d'erreur géant en haut à gauche --}}
            <div class="font-mono font-black text-6xl sm:text-8xl md:text-9xl tracking-tight text-[#38E2F6] drop-shadow-[0_0_40px_rgba(56,226,246,0.5)] leading-none select-none">
                500
            </div>

            {{-- Titre juste au-dessous --}}
            <h1 class="mt-2 font-mono font-extrabold text-2xl sm:text-4xl text-white tracking-wide uppercase flex items-center gap-3">
                <i class="bi bi-lightning-charge-fill text-[#38E2F6]"></i>
                INTERNAL SERVER ERROR
            </h1>

            {{-- Message explicatif --}}
            <p class="mt-4 text-slate-300 text-sm sm:text-base leading-relaxed font-sans">
                Une surcharge de foudre a perturbé les flux du serveur ! Nos ninjas d'élite analysent la technique et restaurent l'équilibre du chakra. Merci pour ta patience, shinobi.
            </p>

            {{-- Citation thématique --}}
            <div class="mt-5 pl-4 border-l-2 border-[#38E2F6]/60 bg-[#38E2F6]/5 py-2.5 pr-4 rounded-r-lg">
                <p class="italic text-xs sm:text-sm text-slate-300 font-serif">
                    « Même face à la plus sombre des tempêtes, un shinobi ne renonce jamais. »
                </p>
                <span class="block mt-1 text-[11px] font-mono text-[#38E2F6] uppercase tracking-wider font-semibold">
                    — Kakashi Hatake
                </span>
            </div>

            {{-- Boutons d'action empilés --}}
            <div class="mt-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                <button onclick="window.location.reload()" 
                        class="inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl font-bold text-xs sm:text-sm tracking-wide bg-[#38E2F6] hover:bg-[#20c5d9] text-[#0E0D0F] shadow-lg shadow-[#38E2F6]/30 transition-all hover:scale-[1.02] active:scale-95 text-center cursor-pointer">
                    <i class="bi bi-arrow-clockwise"></i>
                    Réessayer
                </button>

                <a href="{{ auth()->check() ? route('fighters.my-character') : route('dashboard') }}" 
                   class="inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl font-semibold text-xs sm:text-sm tracking-wide bg-[#17161A]/90 hover:bg-[#201F25] text-slate-200 border border-slate-700/80 hover:border-slate-500 transition-all hover:scale-[1.02] active:scale-95 text-center">
                    <i class="bi bi-house-door-fill"></i>
                    Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
