@extends('layouts.app')

@section('title', 'Classement Mondial & Hall of Fame — Otaku Arena')

@section('content')
<div class="space-y-8 pb-12">

    @php
        $userFighter = auth()->check() ? auth()->user()->fighter : null;
        $hasLegends = $hallOfFameLegends->isNotEmpty();
        $isGeneralActive = empty($selectedClan) && empty($selectedVillage) && $filter !== 'clan' && $filter !== 'village';
    @endphp

    <!-- ========================================================================= -->
    <!-- 1. EN-TÊTE « CLASSEMENT MONDIAL DES SHINOBIS » AVEC FILTRES DYNAMIQUES   -->
    <!-- ========================================================================= -->
    <div class="relative overflow-hidden rounded-3xl border border-shinobi-border bg-gradient-to-r from-shinobi-dark via-[#1A1218] to-shinobi-card p-6 sm:p-8 shadow-2xl">
        <div class="pointer-events-none absolute -right-12 -top-12 h-64 w-64 rounded-full bg-shinobi-gold/15 blur-3xl"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-shinobi-gold font-serif text-xl">忍</span>
                    <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-shinobi-gold">PANTHÉON DES HÉROS</span>
                </div>
                <h1 class="font-serif text-3xl sm:text-4xl font-black uppercase text-white tracking-tight">
                    Classement Mondial des Shinobis
                </h1>
                <p class="font-mono text-xs text-slate-400 mt-1">
                    Progression régie par les victoires et défaites aux tournois sur le cycle de 5 jours (Lundi — Vendredi).
                </p>
            </div>

            <!-- Filtres de classement interactifs et dynamiques (Général / Clan / Village) -->
            <div class="flex flex-wrap items-center gap-2 font-mono text-xs">
                <!-- Général -->
                <a href="{{ route('leaderboard.index') }}"
                   class="rounded-xl px-4 py-2 font-bold transition {{ $isGeneralActive ? 'bg-shinobi-gold text-shinobi-dark shadow-lg shadow-shinobi-gold/20' : 'border border-shinobi-border bg-white/5 text-slate-400 hover:text-white hover:bg-white/10' }}">
                    Général
                </a>

                <!-- Par Clan -->
                <a href="{{ route('leaderboard.index', ['filter' => 'clan']) }}"
                   class="rounded-xl px-3.5 py-2 transition {{ ($filter === 'clan' || !empty($selectedClan)) ? 'bg-shinobi-red text-white font-bold shadow-lg shadow-shinobi-red/20' : 'border border-shinobi-border bg-white/5 text-slate-400 hover:text-white hover:bg-white/10' }}">
                    Par Clan {{ !empty($selectedClan) ? '('.$selectedClan.')' : '' }}
                </a>

                <!-- Par Village -->
                <a href="{{ route('leaderboard.index', ['filter' => 'village']) }}"
                   class="rounded-xl px-3.5 py-2 transition {{ ($filter === 'village' || !empty($selectedVillage)) ? 'bg-shinobi-cyan text-shinobi-dark font-bold shadow-lg shadow-shinobi-cyan/20' : 'border border-shinobi-border bg-white/5 text-slate-400 hover:text-white hover:bg-white/10' }}">
                    Par Village {{ !empty($selectedVillage) ? '('.$selectedVillage.')' : '' }}
                </a>
            </div>
        </div>

        <!-- Sous-barre dynamique si filtre Par Clan actif -->
        @if($filter === 'clan' || !empty($selectedClan))
            <div class="relative z-10 mt-5 pt-4 border-t border-shinobi-border/60">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-mono text-[10px] uppercase font-bold text-slate-400 mr-1">Choisir un clan :</span>
                    <a href="{{ route('leaderboard.index', ['filter' => 'clan']) }}"
                       class="rounded-lg px-2.5 py-1 font-mono text-[11px] {{ empty($selectedClan) ? 'bg-white/20 text-white font-bold' : 'bg-white/5 text-slate-400 hover:text-white' }}">
                        Tous
                    </a>
                    @foreach($clans as $c)
                        <a href="{{ route('leaderboard.index', ['filter' => 'clan', 'clan' => $c]) }}"
                           class="rounded-lg px-2.5 py-1 font-mono text-[11px] transition {{ $selectedClan === $c ? 'bg-shinobi-red text-white font-bold' : 'bg-white/5 text-slate-400 hover:text-white hover:bg-white/10' }}">
                            {{ $c }}
                        </a>
                    @endforeach
                    @if(!empty($selectedClan))
                        <a href="{{ route('leaderboard.index') }}" class="font-mono text-[10px] text-slate-400 hover:text-shinobi-red ml-2 inline-flex items-center gap-1">
                            <i class="bi bi-x-lg text-[9px]"></i> <span>Réinitialiser</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <!-- Sous-barre dynamique si filtre Par Village actif -->
        @if($filter === 'village' || !empty($selectedVillage))
            <div class="relative z-10 mt-5 pt-4 border-t border-shinobi-border/60">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-mono text-[10px] uppercase font-bold text-slate-400 mr-1">Choisir un village :</span>
                    <a href="{{ route('leaderboard.index', ['filter' => 'village']) }}"
                       class="rounded-lg px-2.5 py-1 font-mono text-[11px] {{ empty($selectedVillage) ? 'bg-white/20 text-white font-bold' : 'bg-white/5 text-slate-400 hover:text-white' }}">
                        Tous
                    </a>
                    @foreach($villages as $v)
                        <a href="{{ route('leaderboard.index', ['filter' => 'village', 'village' => $v]) }}"
                           class="rounded-lg px-2.5 py-1 font-mono text-[11px] transition {{ $selectedVillage === $v ? 'bg-shinobi-cyan text-slate-950 font-bold' : 'bg-white/5 text-slate-400 hover:text-white hover:bg-white/10' }}">
                            {{ $v }}
                        </a>
                    @endforeach
                    @if(!empty($selectedVillage))
                        <a href="{{ route('leaderboard.index') }}" class="font-mono text-[10px] text-slate-400 hover:text-shinobi-red ml-2 inline-flex items-center gap-1">
                            <i class="bi bi-x-lg text-[9px]"></i> <span>Réinitialiser</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

    </div>

    <!-- ========================================================================= -->
    <!-- 2. GRILLE PRINCIPALE (PODIUM + TABLEAU ET HALL OF FAME CONDITIONNEL)      -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 {{ $hasLegends ? 'lg:grid-cols-12' : 'grid-cols-1' }} gap-6 items-start">

        <!-- ===================================================================== -->
        <!-- ZONE GAUCHE (8 Cols si Hall of Fame présent, 12 Cols pleine largeur)   -->
        <!-- ===================================================================== -->
        <div class="{{ $hasLegends ? 'lg:col-span-8' : 'w-full' }} space-y-6">

            <!-- ================================================================= -->
            <!-- LE PODIUM DES 3 PREMIERS SHINOBIS (ÉTAT PROPRE VACANT PAR DÉFAUT) -->
            <!-- ================================================================= -->
            <div class="glass3d rounded-3xl border border-shinobi-border/70 p-6 pt-10 relative overflow-hidden">
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-shinobi-gold/10 via-transparent to-transparent"></div>

                <div class="grid grid-cols-3 gap-2 sm:gap-4 items-end text-center relative z-10">
                    
                    <!-- 2ÈME PLACE : SOCLE ARGENT (Gauche) -->
                    <div class="space-y-3 pb-2">
                        <div class="relative inline-block">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-full border-2 border-slate-400/50 p-1 bg-shinobi-dark shadow-xl flex items-center justify-center">
                                @if($top2)
                                    <img src="{{ $top2->portrait_path ?? ($top2->source_photo_path ?? asset('images/naruto-hero.png')) }}" alt="{{ $top2->name }}" class="w-full h-full object-cover rounded-full">
                                @else
                                    <div class="w-full h-full rounded-full bg-white/5 flex items-center justify-center text-slate-500 font-serif text-lg">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                @endif
                            </div>
                            <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-6 h-6 rounded-full bg-slate-300 text-shinobi-dark font-mono text-xs font-black flex items-center justify-center shadow">
                                2
                            </span>
                        </div>
                        <div class="min-w-0">
                            @if($top2)
                                <a href="{{ route('fighters.show', $top2) }}" class="font-serif font-bold text-sm sm:text-base text-white hover:text-shinobi-gold transition truncate block">
                                    {{ $top2->name }}
                                </a>
                                <span class="font-mono text-[10px] text-slate-400 block">Clan {{ $top2->clan }}</span>
                                <span class="font-mono text-[10px] text-slate-400 block">Niv. {{ $top2->level }} • {{ $top2->getCalculatedRank() }}</span>
                            @else
                                <h4 class="font-serif font-bold text-sm text-slate-400">Place vacante</h4>
                                <span class="font-mono text-[10px] text-slate-500 block">En attente d'un rival</span>
                            @endif
                        </div>
                        <div class="rounded-xl border border-slate-400/40 bg-slate-500/10 py-3 font-mono text-[10px] sm:text-xs text-slate-300">
                            @if($top2)
                                <span class="font-bold text-white block">{{ number_format($top2->weekly_win_rate, 1) }}% Victoires</span>
                                <span class="text-[9px] text-slate-400">{{ $top2->weekly_tourn_wins }}V - {{ $top2->weekly_tourn_losses }}D</span>
                            @else
                                <span class="font-bold text-slate-500 block">—</span>
                            @endif
                            <span class="text-[9px] text-slate-300 block uppercase font-bold mt-1">Socle d'Argent</span>
                        </div>
                    </div>

                    <!-- 1ÈRE PLACE : GRAND SOCLE OR (Centre, le plus haut) -->
                    <div class="space-y-3 pb-6">
                        <div class="relative inline-block">
                            <!-- Couronne Dorée -->
                            <div class="text-xl text-shinobi-gold -mb-1 animate-bounce flex justify-center">
                                <i class="bi bi-crown-fill"></i>
                            </div>
                            <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto rounded-full border-4 border-shinobi-gold/60 p-1 bg-shinobi-dark shadow-2xl shadow-shinobi-gold/20 flex items-center justify-center">
                                @if($top1)
                                    <img src="{{ $top1->portrait_path ?? ($top1->source_photo_path ?? asset('images/naruto-hero.png')) }}" alt="{{ $top1->name }}" class="w-full h-full object-cover rounded-full">
                                @else
                                    <div class="w-full h-full rounded-full bg-shinobi-gold/5 flex items-center justify-center text-shinobi-gold/50 font-serif text-2xl">
                                        <i class="bi bi-trophy-fill"></i>
                                    </div>
                                @endif
                            </div>
                            <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-7 h-7 rounded-full bg-shinobi-gold text-shinobi-dark font-mono text-sm font-black flex items-center justify-center shadow-lg">
                                1
                            </span>
                        </div>
                        <div class="min-w-0">
                            @if($top1)
                                <a href="{{ route('fighters.show', $top1) }}" class="font-serif font-black text-base sm:text-lg text-shinobi-gold hover:underline truncate block">
                                    {{ $top1->name }}
                                </a>
                                <span class="rounded bg-shinobi-gold/20 text-shinobi-gold px-2 py-0.5 font-mono text-[9px] font-bold uppercase inline-block">
                                    Hokage
                                </span>
                                <span class="font-mono text-[10px] text-slate-300 block mt-1">Clan {{ $top1->clan }} • Niv. {{ $top1->level }}</span>
                            @else
                                <h3 class="font-serif font-black text-base text-slate-400">Place vacante</h3>
                                <span class="rounded bg-white/10 text-slate-400 px-2 py-0.5 font-mono text-[9px] font-bold uppercase inline-block">
                                    Hokage
                                </span>
                                <span class="font-mono text-[10px] text-slate-500 block mt-1">En attente de prétendant</span>
                            @endif
                        </div>
                        <div class="rounded-2xl border-2 border-shinobi-gold bg-gradient-to-b from-amber-500/20 to-shinobi-dark py-4 font-mono text-xs text-shinobi-gold shadow-xl shadow-shinobi-gold/20">
                            @if($top1)
                                <span class="font-black text-sm sm:text-base text-white block">{{ number_format($top1->weekly_win_rate, 1) }}% Victoires</span>
                                <span class="text-[10px] text-amber-300 font-bold uppercase tracking-wider block">
                                    {{ $top1->weekly_tourn_wins }}V - {{ $top1->weekly_tourn_losses }}D en tournoi
                                </span>
                            @else
                                <span class="font-black text-slate-500 block">—</span>
                            @endif
                            <span class="text-[10px] text-shinobi-gold font-bold uppercase tracking-wider block mt-1">Socle d'Or</span>
                        </div>
                    </div>

                    <!-- 3ÈME PLACE : SOCLE BRONZE (Droite) -->
                    <div class="space-y-3 pb-0">
                        <div class="relative inline-block">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-full border-2 border-amber-700/50 p-1 bg-shinobi-dark shadow-xl flex items-center justify-center">
                                @if($top3)
                                    <img src="{{ $top3->portrait_path ?? ($top3->source_photo_path ?? asset('images/naruto-hero.png')) }}" alt="{{ $top3->name }}" class="w-full h-full object-cover rounded-full">
                                @else
                                    <div class="w-full h-full rounded-full bg-white/5 flex items-center justify-center text-slate-500 font-serif text-lg">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                @endif
                            </div>
                            <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-6 h-6 rounded-full bg-amber-700 text-white font-mono text-xs font-black flex items-center justify-center shadow">
                                3
                            </span>
                        </div>
                        <div class="min-w-0">
                            @if($top3)
                                <a href="{{ route('fighters.show', $top3) }}" class="font-serif font-bold text-sm sm:text-base text-white hover:text-amber-500 transition truncate block">
                                    {{ $top3->name }}
                                </a>
                                <span class="font-mono text-[10px] text-slate-400 block">Clan {{ $top3->clan }}</span>
                                <span class="font-mono text-[10px] text-slate-400 block">Niv. {{ $top3->level }} • {{ $top3->getCalculatedRank() }}</span>
                            @else
                                <h4 class="font-serif font-bold text-sm text-slate-400">Place vacante</h4>
                                <span class="font-mono text-[10px] text-slate-500 block">En attente d'un rival</span>
                            @endif
                        </div>
                        <div class="rounded-xl border border-amber-700/50 bg-amber-950/20 py-2.5 font-mono text-[10px] sm:text-xs text-amber-500">
                            @if($top3)
                                <span class="font-bold text-white block">{{ number_format($top3->weekly_win_rate, 1) }}% Victoires</span>
                                <span class="text-[9px] text-amber-600">{{ $top3->weekly_tourn_wins }}V - {{ $top3->weekly_tourn_losses }}D</span>
                            @else
                                <span class="font-bold text-slate-500 block">—</span>
                            @endif
                            <span class="text-[9px] text-amber-500 block uppercase font-bold mt-1">Socle de Bronze</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ================================================================= -->
            <!-- TABLEAU DES RANGS                                                 -->
            <!-- ================================================================= -->
            <div class="glass3d rounded-2xl border border-shinobi-border/70 p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-3 flex-wrap gap-2">
                    <div>
                        <span class="font-mono text-xs font-bold uppercase tracking-wider text-slate-300 block">
                            Classement Officiel
                            @if($selectedClan) • Clan {{ $selectedClan }} @endif
                            @if($selectedVillage) • Village de {{ $selectedVillage }} @endif
                        </span>
                    </div>
                    <span class="font-mono text-[10px] text-slate-400 bg-white/5 border border-shinobi-border px-2.5 py-1 rounded-lg">
                        {{ $fighters->total() }} SHINOBI{{ $fighters->total() > 1 ? 'S' : '' }} QUALIFIÉ{{ $fighters->total() > 1 ? 'S' : '' }}
                    </span>
                </div>

                <!-- Table Responsive des Ninjas -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left font-mono text-xs">
                        <thead>
                            <tr class="border-b border-shinobi-border/60 text-slate-500 text-[10px] uppercase">
                                <th class="pb-2 pl-2">#</th>
                                <th class="pb-2">Shinobi</th>
                                <th class="pb-2 text-center">Niveau & Rang</th>
                                <th class="pb-2 text-center">Tournoi</th>
                                <th class="pb-2 text-right">Points d'Arène (XP)</th>
                                <th class="pb-2 pr-2 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-shinobi-border/40">
                            @forelse($fighters as $index => $f)
                                @php
                                    $rank = $fighters->firstItem() + $index;
                                    $isCurrentUser = $userFighter && $userFighter->id === $f->id;
                                @endphp
                                <tr class="transition {{ $isCurrentUser ? 'bg-shinobi-gold/10 ring-1 ring-shinobi-gold text-white font-bold' : 'hover:bg-white/5 text-slate-300' }}">
                                    <!-- Rang -->
                                    <td class="py-3 pl-2 font-bold {{ $rank <= 3 ? 'text-shinobi-gold' : 'text-slate-400' }}">
                                        {{ sprintf('%02d', $rank) }}
                                    </td>

                                    <!-- Shinobi (Avatar + Nom + Clan + Village) -->
                                    <td class="py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-shinobi-dark border border-shinobi-border overflow-hidden shrink-0">
                                                <img src="{{ $f->portrait_path ?? ($f->source_photo_path ?? asset('images/naruto-hero.png')) }}" alt="{{ $f->name }}" class="w-full h-full object-cover">
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-serif font-bold text-white text-xs block truncate">{{ $f->name }}</span>
                                                    @if($isCurrentUser)
                                                        <span class="rounded bg-shinobi-gold text-shinobi-dark font-mono text-[9px] font-black px-1.5 py-0.2">VOUS</span>
                                                    @endif
                                                </div>
                                                <span class="text-[10px] text-slate-500">
                                                    Clan {{ $f->clan }} • {{ $f->village ?? 'Konoha' }} • {{ $f->user?->username ?? 'Shinobi' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Niveau & Rang Canon -->
                                    <td class="py-3 text-center">
                                        <span class="rounded bg-white/5 border border-shinobi-border px-2 py-0.5 text-[10px]">
                                            Niv. {{ $f->level }} • <strong class="text-white">{{ $f->getCalculatedRank() }}</strong>
                                        </span>
                                    </td>

                                    <!-- Colonne Tournoi -->
                                    <td class="py-3 text-center">
                                        @if($f->weekly_tourn_total > 0)
                                            <div class="inline-flex items-center gap-1.5">
                                                <span class="font-bold {{ $f->weekly_win_rate >= 50 ? 'text-emerald-400' : 'text-shinobi-red' }}">
                                                    {{ number_format($f->weekly_win_rate, 1) }}%
                                                </span>
                                                <span class="text-[10px] text-slate-400">
                                                    ({{ $f->weekly_tourn_wins }}V - {{ $f->weekly_tourn_losses }}D)
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-slate-500 text-xs">—</span>
                                        @endif

                                        {{-- Affichage uniquement le dernier jour en texte simple sans emoji --}}
                                        @if($isLastDay && $f->tournaments_won_count > 0)
                                            <span class="block text-[10px] text-shinobi-gold font-bold mt-0.5">
                                                {{ $f->tournaments_won_count }} tournoi(s) remporté(s)
                                            </span>
                                        @endif
                                    </td>

                                    <!-- XP Total -->
                                    <td class="py-3 text-right font-bold text-white">
                                        {{ number_format($f->xp, 0, ',', ' ') }} pts
                                    </td>

                                    <!-- Action -->
                                    <td class="py-3 pr-2 text-right">
                                        <a href="{{ route('fighters.show', $f) }}" class="rounded-lg border border-shinobi-border bg-white/5 hover:bg-white/10 px-2.5 py-1 text-[10px] font-semibold text-slate-300 hover:text-white transition">
                                            Voir Databook
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 font-mono text-xs">
                                        « Aucun shinobi de ce clan n’a été recensé dans les archives de l’Arène. »
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($fighters->hasPages())
                    <div class="pt-3 border-t border-shinobi-border/60">
                        {{ $fighters->links() }}
                    </div>
                @endif
            </div>

        </div>

        <!-- ===================================================================== -->
        <!-- ZONE DROITE : HALL OF FAME (AFFICHÉ SEULEMENT SI JOUEURS QUALIFIÉS)    -->
        <!-- ===================================================================== -->
        @if($hasLegends)
            <aside class="lg:col-span-4 space-y-4">
                
                <div class="rounded-3xl border border-shinobi-gold/40 bg-gradient-to-b from-[#1E1912] via-shinobi-card to-shinobi-dark p-5 space-y-4 shadow-2xl relative overflow-hidden">
                    <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-shinobi-gold/10 blur-2xl"></div>

                    <div class="flex items-center justify-between border-b border-shinobi-gold/30 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-shinobi-gold text-lg"><i class="bi bi-trophy-fill"></i></span>
                            <div>
                                <h3 class="font-serif font-black uppercase text-base text-shinobi-gold tracking-wide">Hall of Fame</h3>
                                <span class="font-mono text-[9px] text-slate-400">LÉGENDES OFFICIELLES DE L'ARÈNE</span>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des Parchemins Dorés des Vrais Joueurs Légendaires -->
                    <div class="space-y-3 font-mono text-xs">
                        @foreach($hallOfFameLegends as $legend)
                            <div class="rounded-2xl border border-[#DEC4B0] bg-[#F5E0D7] p-3 text-[#181310] shadow-md hover:scale-[1.02] transition-transform space-y-1.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-10 h-10 rounded-full bg-shinobi-dark border border-[#B8967A] overflow-hidden shrink-0">
                                        <img src="{{ $legend->portrait_path ?? ($legend->source_photo_path ?? asset('images/naruto-hero.png')) }}" alt="{{ $legend->name }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('fighters.show', $legend) }}" class="font-serif font-black text-sm text-[#18120D] block truncate hover:underline">
                                            {{ $legend->name }}
                                        </a>
                                        <span class="font-mono text-[10px] text-[#A13524] font-bold block">
                                            {{ $legend->tournaments_won_count }} titre(s) • Rang {{ $legend->getCalculatedRank() }}
                                        </span>
                                    </div>
                                </div>
                                <p class="font-serif italic text-[11px] text-[#483327] leading-tight pt-1 border-t border-[#DEC1B0]">
                                    « Maître du clan {{ $legend->clan }} — Vainqueur des tournois et légende vivante. »
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <!-- Critères d'entrée affichés pour guider les prétendants -->
                    <div class="pt-3 border-t border-shinobi-gold/20 text-center font-serif text-[11px] text-slate-400 italic">
                        « Conditions d'intronisation au Panthéon : Avoir remporté au moins 1 grand Tournoi officiel et avoir atteint le rang Jōnin (Niveau 10+). »
                    </div>
                </div>

            </aside>
        @endif

    </div>

</div>
@endsection
