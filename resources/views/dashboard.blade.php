@extends('layouts.app')

@section('content')
<!-- Hero Section Pleine Largeur Responsive -->
<section class="relative w-full overflow-hidden text-[#181310] min-h-[calc(100vh-4rem)] flex flex-col justify-between p-5 sm:p-10 md:p-14 lg:p-20">
    
    <!-- Image de fond naruto-hero (fond complet immersif) -->
    <img src="{{ asset('images/naruto-hero.png') }}" 
         alt="Naruto Examen Chunin" 
         class="absolute inset-0 w-full h-full object-cover object-[75%_top] sm:object-right md:object-center pointer-events-none select-none">

    <!-- Voile d'ombrage adaptatif : sombre/parchemin en mobile pour faire ressortir le texte, dégradé fondu en desktop -->
    <div class="absolute inset-0 bg-gradient-to-t from-[#F5E0D7] via-[#F5E0D7]/85 to-black/30 sm:bg-gradient-to-r sm:from-[#F5E0D7]/95 sm:via-[#F5E0D7]/65 sm:to-transparent pointer-events-none"></div>

    <!-- Contenu Principal posé par dessus (Titre, sous-texte & CTAs) -->
    <div class="relative z-10 w-full max-w-xl space-y-4 sm:space-y-6 my-auto pt-4 sm:pt-6">
        
        <!-- Titre Officiel Exact de la Maquette -->
        <h1 class="font-serif font-black uppercase text-2xl sm:text-4xl md:text-5xl lg:text-6xl leading-[1.08] text-[#120F0D] tracking-tight text-center sm:text-left">
            L'ARÈNE SHINOBI<br>
            DES TOURNOIS<br>
            DE L'EXAMEN CHŪNIN
        </h1>

        <!-- Paragraphe fidèle à la maquette -->
        <p class="font-sans text-xs sm:text-sm md:text-base text-[#3A2D25] max-w-lg leading-relaxed font-semibold text-center sm:text-left mx-auto sm:mx-0">
            Crée ton ninja, maîtrise les jutsus, affronte les meilleurs dans des tournois à élimination directe.
        </p>

        <!-- Boutons CTA Exacts : Pleine largeur empilés en mobile, côte à côte en desktop -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2 max-w-md mx-auto sm:mx-0">
            <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-shinobi-red px-6 sm:px-8 py-3.5 font-mono text-sm sm:text-base font-bold text-white shadow-xl shadow-shinobi-red/30 hover:bg-shinobi-red-hover hover:scale-[1.02] active:scale-[0.98] transition text-center">
                Jouer maintenant !
            </a>
            <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl border border-[#CBB0A0] bg-white/85 backdrop-blur-sm px-6 sm:px-8 py-3.5 font-mono text-sm sm:text-base font-semibold text-[#2D231E] hover:bg-white transition text-center">
                Découvrir
            </a>
        </div>
    </div>

    <!-- 4 Badges de Métriques : MASQUÉS SUR MOBILE (hidden), VISIBLES EN DESKTOP (sm:grid) -->
    <div class="relative z-10 mt-10 pt-6 sm:pt-8 border-t border-[#DEC1B0]/80 hidden sm:grid grid-cols-2 md:grid-cols-4 gap-6 text-left max-w-5xl">
        
        <!-- 10K+ Joueurs actifs -->
        <div class="flex items-center gap-3 justify-start">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/80 backdrop-blur-xs border border-[#D5B8A6] text-shinobi-red shadow-sm">
                <i class="bi bi-people-fill text-xl"></i>
            </div>
            <div>
                <span class="block font-mono text-lg sm:text-xl font-bold text-[#15100D] leading-none">10K+</span>
                <span class="block font-sans text-xs font-bold text-[#4B3B30] mt-1">Joueurs actifs</span>
            </div>
        </div>

        <!-- 50+ Tournois mensuels -->
        <div class="flex items-center gap-3 justify-start">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/80 backdrop-blur-xs border border-[#D5B8A6] text-shinobi-red shadow-sm">
                <i class="bi bi-trophy-fill text-xl"></i>
            </div>
            <div>
                <span class="block font-mono text-lg sm:text-xl font-bold text-[#15100D] leading-none">50+</span>
                <span class="block font-sans text-xs font-bold text-[#4B3B30] mt-1">Tournois mensuels</span>
            </div>
        </div>

        <!-- 5 Éléments -->
        <div class="flex items-center gap-3 justify-start">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/80 backdrop-blur-xs border border-[#D5B8A6] text-shinobi-red shadow-sm">
                <i class="bi bi-fire text-xl"></i>
            </div>
            <div>
                <span class="block font-mono text-lg sm:text-xl font-bold text-[#15100D] leading-none">5</span>
                <span class="block font-sans text-xs font-bold text-[#4B3B30] mt-1">Éléments</span>
            </div>
        </div>

        <!-- 100% Esprit shinobi -->
        <div class="flex items-center gap-3 justify-start">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/80 backdrop-blur-xs border border-[#D5B8A6] text-shinobi-red shadow-sm">
                <i class="bi bi-shield-shaded text-xl"></i>
            </div>
            <div>
                <span class="block font-mono text-lg sm:text-xl font-bold text-[#15100D] leading-none">100%</span>
                <span class="block font-sans text-xs font-bold text-[#4B3B30] mt-1">Esprit shinobi</span>
            </div>
        </div>

    </div>

</section>
@endsection
