@extends('layouts.app', ['title' => '404 — Not Found | Otaku Arena', 'isErrorPage' => true])

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Permanent+Marker&family=Sedgwick+Ave+Display&display=swap" rel="stylesheet">
<style>
    .font-brush {
        font-family: 'Permanent Marker', 'Sedgwick Ave Display', cursive, sans-serif;
    }
</style>
@endpush

@section('content')
<div class="relative w-full min-h-[calc(100vh-4rem)] flex items-center bg-[#0E0D0F] overflow-hidden">
    {{-- Image d'arrière-plan 16:9 cinématographique officielle (Madara) --}}
    <div class="absolute inset-0 z-0 pointer-events-none">
        <img src="{{ asset('images/errors/404-madara.jpg') }}" 
             alt="Madara Uchiha - Fond 404" 
             class="w-full h-full object-cover object-center">
        {{-- Dégradé sombre sur la gauche pour détacher le texte --}}
        <div class="absolute inset-0 bg-gradient-to-r from-[#0E0D0F]/90 via-[#0E0D0F]/50 to-transparent w-full md:w-1/2"></div>
    </div>

    {{-- Calligraphie japonaise verticale à droite (うちは マダラ) --}}
    <div class="hidden lg:flex flex-col items-center absolute right-8 sm:right-14 top-1/2 -translate-y-1/2 z-10 select-none pointer-events-none opacity-80">
        <span class="text-xs sm:text-sm font-mono tracking-widest text-slate-300 [writing-mode:vertical-rl] mb-4">
            うちは マダラ
        </span>
        <div class="w-7 h-7 rounded-full border border-slate-500/70 bg-black/60 flex items-center justify-center p-1 shadow-lg">
            <span class="w-3.5 h-3.5 rounded-full bg-red-600"></span>
        </div>
    </div>

    {{-- Bloc de contenu gauche fidèle au modèle --}}
    <div class="relative z-10 max-w-7xl mx-auto px-6 sm:px-10 lg:px-16 py-10 sm:py-16 w-full">
        <div class="max-w-xl">
            
            {{-- Grand code d'erreur 404 en police Brush Shonen Blanche --}}
            <div class="font-brush text-7xl sm:text-9xl md:text-[9.5rem] leading-none text-white drop-shadow-[0_4px_25px_rgba(255,255,255,0.4)] select-none italic tracking-normal">
                404
            </div>

            {{-- Titre juste dessous en Brush blanc --}}
            <h1 class="font-brush text-2xl sm:text-3xl md:text-4xl text-white tracking-wider uppercase mt-1 drop-shadow-[0_2px_15px_rgba(255,255,255,0.3)]">
                NOT FOUND
            </h1>

            {{-- Phrase d'accroche --}}
            <h2 class="mt-4 font-sans font-bold text-base sm:text-lg text-white">
                Cette page n'existe pas... ou a disparu dans une autre dimension.
            </h2>

            {{-- Explication --}}
            <p class="mt-1.5 text-xs sm:text-sm text-slate-300 font-sans leading-relaxed max-w-md">
                Même les plus grands ninjas se perdent parfois.<br class="hidden sm:inline">
                Reviens à l'accueil ou explore l'arène.
            </p>

            {{-- Boutons d'action empilés --}}
            <div class="mt-7 flex flex-col items-start gap-3 w-full sm:w-auto">
                <a href="{{ auth()->check() ? route('fighters.my-character') : route('dashboard') }}" 
                   class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full font-sans font-bold text-xs uppercase tracking-wider bg-[#E50914] hover:bg-red-700 text-white shadow-lg shadow-red-900/40 transition-all hover:scale-105 active:scale-95">
                    <i class="bi bi-house-door-fill text-sm"></i>
                    <span>Retour à l'accueil</span>
                </a>

                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full font-sans font-semibold text-xs tracking-wider bg-black/60 hover:bg-white/10 text-slate-200 border border-slate-600/70 hover:border-slate-400 transition-all hover:scale-105 active:scale-95">
                    <i class="bi bi-eye text-sm"></i>
                    <span>Découvrir le jeu</span>
                </a>
            </div>

            {{-- Citation en bas --}}
            <div class="mt-10 max-w-md">
                <p class="italic text-[11px] sm:text-xs text-slate-400 font-sans leading-relaxed">
                    « Le monde est rempli d'illusions... Et tu es l'une d'elles. »
                </p>
                <span class="block mt-1 text-[10px] font-sans font-semibold text-slate-400">
                    — Madara Uchiha
                </span>
            </div>

        </div>
    </div>
</div>
@endsection
