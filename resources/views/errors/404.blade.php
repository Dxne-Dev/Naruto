@extends('layouts.app', ['title' => '404 — Not Found | Otaku Arena', 'isErrorPage' => true])

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Permanent+Marker&family=Sedgwick+Ave+Display&display=swap" rel="stylesheet">
<style>
    .font-brush {
        font-family: 'Permanent Marker', 'Sedgwick Ave Display', cursive, sans-serif;
    }
    .text-shadow-white {
        text-shadow: 0 0 35px rgba(255, 255, 255, 0.5), 0 0 70px rgba(255, 255, 255, 0.25);
    }
</style>
@endpush

@section('content')
<div class="relative w-full min-h-[calc(100vh-4rem)] flex items-center bg-[#0E0D0F] overflow-hidden">
    {{-- Image d'arrière-plan 16:9 officielle Madara --}}
    <div class="absolute inset-0 z-0 pointer-events-none">
        <img src="{{ asset('images/errors/404-madara.jpg') }}" 
             alt="Madara Uchiha - 404" 
             class="w-full h-full object-cover object-center">
        {{-- Dégradé sombre pour détacher le texte à gauche --}}
        <div class="absolute inset-0 bg-gradient-to-r from-[#0E0D0F]/95 via-[#0E0D0F]/65 to-transparent w-full md:w-3/5"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#0E0D0F] via-transparent to-[#0E0D0F]/30"></div>
    </div>

    {{-- Calligraphie japonaise : masquée sur mobile, positionnée à droite du personnage et légèrement vers le bas --}}
    <div class="hidden md:flex flex-col items-center absolute right-6 lg:right-10 bottom-12 lg:bottom-16 z-20 select-none pointer-events-none">
        <span class="text-sm lg:text-base font-mono tracking-widest text-slate-200 [writing-mode:vertical-rl] mb-3 drop-shadow-[0_2px_8px_rgba(0,0,0,0.9)] font-semibold">
            うちは マダラ
        </span>
        <div class="w-8 h-8 rounded-full border border-slate-500 bg-[#0E0D0F]/90 flex items-center justify-center p-1 shadow-lg">
            <span class="w-3.5 h-3.5 rounded-full bg-[#E50914]"></span>
        </div>
    </div>

    {{-- Bloc de contenu gauche --}}
    <div class="relative z-10 max-w-7xl mx-auto px-6 sm:px-12 lg:px-20 py-12 sm:py-20 w-full">
        <div class="max-w-2xl">
            
            {{-- Code d'erreur géant 404 en blanc avec ombre portée --}}
            <div class="font-brush text-8xl sm:text-[11rem] md:text-[13rem] lg:text-[14rem] leading-none text-white text-shadow-white select-none italic tracking-tight"
                 style="color: #FFFFFF;">
                404
            </div>

            {{-- Titre juste dessous en blanc --}}
            <h1 class="font-brush text-2xl sm:text-4xl md:text-5xl text-white tracking-wider uppercase mt-1 drop-shadow-[0_4px_20px_rgba(255,255,255,0.4)]"
                style="color: #FFFFFF;">
                NOT FOUND
            </h1>

            {{-- Phrase d'accroche --}}
            <h2 class="mt-5 font-sans font-extrabold text-lg sm:text-xl md:text-2xl text-white drop-shadow">
                Cette page n'existe pas... ou a disparu dans une autre dimension.
            </h2>

            {{-- Explication --}}
            <p class="mt-2 text-sm sm:text-base text-slate-300 font-sans leading-relaxed max-w-lg">
                Même les plus grands ninjas se perdent parfois.<br class="hidden sm:inline">
                Reviens à l'accueil ou explore l'arène.
            </p>

            {{-- Boutons d'action empilés --}}
            <div class="mt-8 flex flex-col items-start gap-3.5 w-full sm:w-auto">
                <a href="{{ auth()->check() ? route('fighters.my-character') : route('dashboard') }}" 
                   class="inline-flex items-center justify-center gap-2.5 px-8 py-3 rounded-full font-sans font-bold text-sm tracking-wide text-white border border-[#ff3b47] shadow-[0_4px_20px_rgba(229,9,20,0.5)] transition-all hover:scale-105 active:scale-95"
                   style="background-color: #E50914;">
                    <i class="bi bi-house-door-fill text-base"></i>
                    <span>Retour à l'accueil</span>
                </a>

                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center justify-center gap-2.5 px-8 py-3 rounded-full font-sans font-semibold text-sm tracking-wide bg-black/75 hover:bg-white/10 text-slate-100 border border-slate-600 hover:border-slate-300 transition-all hover:scale-105 active:scale-95 backdrop-blur-sm">
                    <i class="bi bi-eye text-base"></i>
                    <span>Découvrir le jeu</span>
                </a>
            </div>

            {{-- Citation en bas --}}
            <div class="mt-12 max-w-lg">
                <p class="italic text-xs sm:text-sm text-slate-400 font-sans leading-relaxed">
                    « Le monde est rempli d'illusions... Et tu es l'une d'elles. »
                </p>
                <span class="block mt-1 text-xs font-sans font-semibold text-slate-400">
                    — Madara Uchiha
                </span>
            </div>

        </div>
    </div>
</div>
@endsection
