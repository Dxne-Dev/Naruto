@extends('layouts.app', ['title' => '500 — Erreur Interne du Serveur | Otaku Arena'])

@section('content')
<main class="relative flex-1 min-h-[calc(100vh-4rem)] flex items-center bg-[#0E0D0F] overflow-hidden">
    {{-- Background Artwork 16:9 avec Kakashi à droite, espace sombre à gauche --}}
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('images/errors/500-kakashi.jpg') }}" 
             alt="Kakashi Hatake - Erreur Interne du Serveur" 
             class="w-full h-full object-cover object-right sm:object-center opacity-70 filter brightness-90">
        {{-- Dégradés cinématographiques avec nuance bleue chakra à gauche --}}
        <div class="absolute inset-0 bg-gradient-to-r from-[#0E0D0F] via-[#0E0D0F]/85 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#0E0D0F] via-transparent to-[#0E0D0F]/60"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_left,_var(--tw-gradient-stops))] from-[#0E0D0F] via-[#0E0D0F]/60 to-transparent"></div>
    </div>

    {{-- Contenu aligné à gauche --}}
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 w-full">
        <div class="max-w-2xl">
            {{-- Code d'erreur géant en haut à gauche --}}
            <div class="relative inline-block">
                <span class="block font-mono font-black text-7xl sm:text-9xl md:text-[10rem] tracking-tighter text-[#38E2F6] drop-shadow-[0_0_35px_rgba(56,226,246,0.6)] leading-none select-none">
                    500
                </span>
                <span class="absolute -top-3 -right-6 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold tracking-widest uppercase bg-[#38E2F6]/20 border border-[#38E2F6]/50 text-[#38E2F6]">
                    INTERNAL ERROR
                </span>
            </div>

            {{-- Titre juste au-dessous --}}
            <h1 class="mt-4 font-mono font-extrabold text-2xl sm:text-4xl text-white tracking-wide uppercase flex items-center gap-3">
                <i class="bi bi-lightning-charge-fill text-[#38E2F6]"></i>
                INTERNAL SERVER ERROR
            </h1>

            {{-- Message explicatif --}}
            <p class="mt-4 text-slate-300 text-base sm:text-lg leading-relaxed font-sans">
                Une surcharge de foudre a perturbé les flux du serveur ! Nos ninjas d'élite analysent la technique et restaurent l'équilibre du chakra. Merci pour ta patience, shinobi.
            </p>

            {{-- Citation thématique --}}
            <div class="mt-5 pl-4 border-l-2 border-[#38E2F6]/60 bg-[#38E2F6]/5 py-2.5 pr-4 rounded-r-lg max-w-xl">
                <p class="italic text-sm sm:text-base text-slate-300 font-serif">
                    « Même face à la plus sombre des tempêtes, un shinobi ne renonce jamais. »
                </p>
                <span class="block mt-1 text-xs font-mono text-[#38E2F6] uppercase tracking-wider font-semibold">
                    — Kakashi Hatake
                </span>
            </div>

            {{-- Boutons d'action empilés --}}
            <div class="mt-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                <button onclick="window.location.reload()" 
                        class="inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl font-bold text-sm tracking-wide bg-[#38E2F6] hover:bg-[#20c5d9] text-[#0E0D0F] shadow-lg shadow-[#38E2F6]/30 transition-all hover:scale-[1.02] active:scale-95 text-center cursor-pointer">
                    <i class="bi bi-arrow-clockwise"></i>
                    Réessayer
                </button>

                <a href="{{ auth()->check() ? route('fighters.my-character') : route('dashboard') }}" 
                   class="inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl font-semibold text-sm tracking-wide bg-[#17161A] hover:bg-[#201F25] text-slate-200 border border-slate-700/60 hover:border-slate-500 transition-all hover:scale-[1.02] active:scale-95 text-center">
                    <i class="bi bi-house-door-fill"></i>
                    Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</main>
@endsection
