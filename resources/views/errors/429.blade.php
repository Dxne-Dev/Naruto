@extends('layouts.app', ['title' => '429 — Trop de requêtes | Otaku Arena', 'isErrorPage' => true])

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Permanent+Marker&family=Sedgwick+Ave+Display&display=swap" rel="stylesheet">
<style>
    .font-brush {
        font-family: 'Permanent Marker', 'Sedgwick Ave Display', cursive, sans-serif;
    }
    .text-brush-429 {
        font-size: clamp(5.5rem, 13vw, 11rem);
        line-height: 0.9;
        color: #E50914 !important;
        text-shadow: 0 4px 30px rgba(229, 9, 20, 0.6);
    }
    .text-brush-title {
        font-size: clamp(1.75rem, 4.5vw, 3.25rem);
        line-height: 1.1;
        color: #E50914 !important;
        text-shadow: 0 2px 20px rgba(229, 9, 20, 0.5);
    }
</style>
@endpush

@section('content')
<div class="relative w-full min-h-[calc(100vh-4rem)] flex items-center bg-[#0E0D0F] overflow-hidden">
    {{-- Image d'arrière-plan 16:9 officielle Itachi --}}
    <div class="absolute inset-0 z-0 pointer-events-none">
        <img src="{{ asset('images/errors/429-itachi.jpg') }}" 
             alt="Itachi Uchiha - 429" 
             class="w-full h-full object-cover object-center">
        {{-- Dégradé noir pour détacher le texte à gauche sans dénaturer la scène --}}
        <div class="absolute inset-0 bg-gradient-to-r from-[#0E0D0F]/90 via-[#0E0D0F]/50 to-transparent w-full md:w-1/2"></div>
    </div>

    {{-- Calligraphie japonaise : masquée sur mobile, sur web placée à droite de la tête d'Itachi (mi-hauteur à droite) --}}
    <div class="hidden md:flex flex-col items-center absolute right-6 sm:right-10 lg:right-14 top-1/2 -translate-y-8 z-20 select-none pointer-events-none">
        <span class="text-sm sm:text-base font-mono tracking-widest text-slate-200 [writing-mode:vertical-rl] mb-3 drop-shadow-[0_2px_8px_rgba(0,0,0,0.95)] font-semibold">
            うちは イタチ
        </span>
        <div class="w-7 h-7 rounded-full border border-[#E50914] bg-[#0E0D0F]/90 flex items-center justify-center p-1 shadow-[0_0_15px_rgba(229,9,20,0.7)]">
            <span class="w-3.5 h-3.5 rounded-full bg-[#E50914]"></span>
        </div>
    </div>

    {{-- Bloc de contenu gauche aéré et fidèle au modèle design-example --}}
    <div class="relative z-10 max-w-7xl mx-auto px-6 sm:px-12 lg:px-20 py-12 sm:py-16 w-full">
        <div class="max-w-xl">
            
            {{-- Grand code d'erreur 429 Brush Rouge --}}
            <div class="font-brush text-brush-429 select-none italic tracking-normal">
                429
            </div>

            {{-- Titre juste dessous en Brush Rouge --}}
            <h1 class="font-brush text-brush-title uppercase tracking-wider mt-1">
                TOO MANY REQUESTS
            </h1>

            {{-- Phrase d'accroche --}}
            <h2 class="mt-5 font-sans font-bold text-base sm:text-lg md:text-xl text-white">
                Tu as été un peu trop rapide, shinobi !
            </h2>

            {{-- Explication aérée --}}
            <p class="mt-2 text-xs sm:text-sm text-slate-300 font-sans leading-relaxed max-w-md">
                Le serveur est actuellement surchargé par trop de requêtes.<br class="hidden sm:inline">
                Ralentis un peu, respire... et reviens dans quelques instants.
            </p>

            {{-- Boutons d'action empilés nettement séparés --}}
            <div class="mt-7 flex flex-col items-start gap-3 w-full sm:w-auto">
                <a href="{{ auth()->check() ? route('fighters.my-character') : route('dashboard') }}" 
                   class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full font-sans font-bold text-xs uppercase tracking-wider text-white border border-[#ff3b47] shadow-[0_4px_20px_rgba(229,9,20,0.4)] transition-all hover:scale-105 active:scale-95"
                   style="background-color: #E50914 !important;">
                    <i class="bi bi-house-door-fill text-sm"></i>
                    <span>Retour à l'accueil</span>
                </a>

                <button onclick="window.location.reload()" 
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full font-sans font-semibold text-xs tracking-wider bg-black/70 hover:bg-white/10 text-slate-200 border border-slate-600/80 hover:border-slate-400 transition-all hover:scale-105 active:scale-95 cursor-pointer backdrop-blur-sm">
                    <i class="bi bi-arrow-clockwise text-sm"></i>
                    <span>Réessayer</span>
                </button>
            </div>

            {{-- Citation en bas --}}
            <div class="mt-9 max-w-md">
                <p class="italic text-[11px] sm:text-xs text-slate-400 font-sans leading-relaxed">
                    « La patience est une arme aussi puissante que le chakra. »
                </p>
                <span class="block mt-1 text-[10px] font-sans font-semibold text-slate-400">
                    — Itachi Uchiha
                </span>
            </div>

        </div>
    </div>
</div>
@endsection
