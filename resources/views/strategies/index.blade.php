@extends('layouts.app')

@section('title', 'Stratégies — ' . $fighter->name)

@section('content')
@php
    $initialStance = in_array($fighter->strategy_stance ?? 'balanced', ['aggressive', 'offensive']) ? 'aggressive' : (($fighter->strategy_stance ?? 'balanced') === 'defensive' ? 'defensive' : 'balanced');
    $initialPriority = in_array($fighter->jutsu_priority_type ?? 'highest_power', ['auto', 'highest_power']) ? 'highest_power' : ($fighter->jutsu_priority_type ?? 'highest_power');
    $currentProfile = $profiles[$initialStance] ?? $profiles['balanced'];
    $hasPaidPass = (bool) ($fighter->user?->has_paid_microtransaction);
@endphp

<div class="space-y-8 pb-12" id="strategy-app">

    <!-- ========================================================================= -->
    <!-- 1. EN-TÊTE DU SHINOBI & PARCHEMIN SENSEI                                  -->
    <!-- ========================================================================= -->
    <div class="relative overflow-hidden rounded-3xl border border-shinobi-border bg-gradient-to-r from-shinobi-dark via-[#1A1218] to-shinobi-card p-6 sm:p-8 shadow-2xl">
        <div class="pointer-events-none absolute -right-12 -top-12 h-64 w-64 rounded-full bg-shinobi-red/15 blur-3xl"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            
            <!-- Profil Gauche -->
            <div class="flex items-center gap-5">
                <div class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden border-2 border-shinobi-red/60 shadow-xl shadow-shinobi-red/20 shrink-0 bg-black">
                    @if($hasPaidPass)
                        <img src="{{ $fighter->portrait_path ?? ($fighter->source_photo_path ?? asset('images/naruto-hero.png')) }}" 
                             alt="{{ $fighter->name }}" 
                             class="w-full h-full object-cover object-top">
                    @else
                        <!-- Portrait Verrouillé (Pass Non Débloqué) -->
                        <div class="relative w-full h-full bg-black select-none">
                            <img src="{{ asset('images/locked-avatar.png') }}" 
                                 alt="Avatar Verrouillé" 
                                 class="w-full h-full object-cover object-center">
                            <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                                <i class="bi bi-lock-fill text-shinobi-gold text-lg drop-shadow"></i>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="space-y-1">
                    <span class="font-mono text-[10px] font-black uppercase tracking-wider text-shinobi-red">
                        {{ strtoupper($fighter->clan) }}
                    </span>
                    <h1 class="font-serif text-3xl sm:text-4xl font-black uppercase text-white tracking-tight">
                        {{ $fighter->name }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-2 font-mono text-xs text-slate-300">
                        <span class="flex items-center gap-1"><i class="bi bi-geo-alt text-shinobi-red"></i> {{ $fighter->village }}</span>
                        <span>•</span>
                        <span class="flex items-center gap-1"><i class="bi bi-shield-shaded text-shinobi-red"></i> Rang : {{ $fighter->getCalculatedRank() }}</span>
                        <span>•</span>
                        <span class="flex items-center gap-1 rounded bg-shinobi-chakra/10 px-2 py-0.5 text-shinobi-chakra border border-shinobi-chakra/30" title="Affinité Élémentaire Principale">
                            Affinité : {{ $fighter->primary_element }}
                        </span>
                        @if($fighter->secondary_element)
                            <span class="flex items-center gap-1 rounded bg-emerald-500/15 px-2 py-0.5 text-emerald-400 border border-emerald-500/30 font-bold" title="Affinité Élémentaire Secondaire Débloquée">
                                <i class="bi bi-sparkles text-xs"></i>
                                <span>{{ $fighter->secondary_element }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Parchemin Conseil du Sensei -->
            <div class="rounded-2xl border border-[#DFC4B5] bg-[#F5E0D7] p-4 text-[#181310] shadow-xl max-w-md relative overflow-hidden shrink-0">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="font-mono text-[9px] font-bold uppercase tracking-wider text-[#A13524] block mb-0.5">Sagesse Tactique</span>
                        <p class="font-serif italic text-xs text-[#3E2B20] leading-relaxed">
                            « La vraie force ne réside pas dans la puissance brute, mais dans la maîtrise de soi et de ses choix. »
                        </p>
                        <span class="font-mono text-[10px] text-[#7A6150] block mt-1">— Registre officiel des Shinobis</span>
                    </div>
                    <div class="w-8 h-8 rounded border-2 border-shinobi-red flex items-center justify-center font-serif text-[10px] font-black text-shinobi-red select-none shrink-0">
                        戦略
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Formulaire de Stratégie Global -->
    <form method="POST" action="{{ route('strategies.update') }}" class="space-y-8">
        @csrf

        <!-- ========================================================================= -->
        <!-- SECTION 1 : POSTURE TACTIQUE PRINCIPALE                                   -->
        <!-- ========================================================================= -->
        <div class="space-y-4">
            <div>
                <span class="font-mono text-[11px] font-bold uppercase tracking-wider text-shinobi-red">1. POSTURE TACTIQUE PRINCIPALE</span>
                <p class="font-mono text-xs text-slate-400 mt-0.5">
                    Choisis la posture qui correspond à ton style de combat. Elle influencera le radar et l'ensemble de tes duels.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                
                <!-- CARTE 1 : OFFENSIF -->
                <label class="cursor-pointer group relative block" onclick="selectStance('aggressive')">
                    <input type="radio" name="strategy_stance" value="aggressive" {{ $initialStance === 'aggressive' ? 'checked' : '' }} class="sr-only stance-radio">
                    
                    <div id="card-stance-aggressive" class="h-full rounded-2xl border transition-all duration-300 p-5 flex flex-col justify-between bg-shinobi-card/90 shadow-xl {{ $initialStance === 'aggressive' ? 'border-shinobi-red bg-gradient-to-b from-red-950/40 to-shinobi-card ring-2 ring-shinobi-red/40' : 'border-shinobi-border hover:border-shinobi-red/50' }}">
                        <div>
                            <!-- Header Posture -->
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-shinobi-red/20 border border-shinobi-red/40 flex items-center justify-center text-shinobi-red text-base">
                                        <i class="bi bi-fire"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-serif font-black text-lg uppercase text-white tracking-wide">OFFENSIF</h3>
                                        <span class="font-mono text-[10px] text-slate-400">Assaut frontal</span>
                                    </div>
                                </div>
                                <span id="badge-stance-aggressive" class="{{ $initialStance === 'aggressive' ? 'inline-block' : 'hidden' }} rounded-full bg-shinobi-red px-2.5 py-0.5 font-mono text-[9px] font-bold text-white uppercase shadow">
                                    Active
                                </span>
                            </div>

                            <p class="font-sans text-xs text-slate-300 leading-relaxed mb-4">
                                Maximise tes dégâts physiques et ninjutsu. Idéal pour éliminer rapidement tes adversaires au tour 1.
                            </p>

                            <!-- Bonus & Malus Chiffrés -->
                            <div class="space-y-1.5 font-mono text-xs border-t border-shinobi-border/60 pt-3">
                                <div class="flex items-center justify-between text-emerald-400">
                                    <span class="flex items-center gap-1.5"><i class="bi bi-plus-circle"></i> Dégâts infligés</span>
                                    <strong>+25%</strong>
                                </div>
                                <div class="flex items-center justify-between text-emerald-400">
                                    <span class="flex items-center gap-1.5"><i class="bi bi-plus-circle"></i> Vitesse d'initiative</span>
                                    <strong>+5 pts</strong>
                                </div>
                                <div class="flex items-center justify-between text-rose-400">
                                    <span class="flex items-center gap-1.5"><i class="bi bi-dash-circle"></i> Esquive & Défense</span>
                                    <strong>-15%</strong>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-3 border-t border-shinobi-border/40 text-center">
                            <span id="btn-stance-aggressive" class="inline-block w-full py-2 rounded-xl font-mono text-xs font-bold transition {{ $initialStance === 'aggressive' ? 'bg-shinobi-red text-white' : 'bg-white/5 text-slate-300 group-hover:bg-white/10' }}">
                                SÉLECTIONNER
                            </span>
                        </div>
                    </div>
                </label>

                <!-- CARTE 2 : ÉQUILIBRÉ -->
                <label class="cursor-pointer group relative block" onclick="selectStance('balanced')">
                    <input type="radio" name="strategy_stance" value="balanced" {{ $initialStance === 'balanced' ? 'checked' : '' }} class="sr-only stance-radio">
                    
                    <div id="card-stance-balanced" class="h-full rounded-2xl border transition-all duration-300 p-5 flex flex-col justify-between bg-shinobi-card/90 shadow-xl {{ $initialStance === 'balanced' ? 'border-shinobi-chakra bg-gradient-to-b from-cyan-950/40 to-shinobi-card ring-2 ring-shinobi-chakra/40' : 'border-shinobi-border hover:border-shinobi-chakra/50' }}">
                        <div>
                            <!-- Header Posture -->
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-shinobi-chakra/20 border border-shinobi-chakra/40 flex items-center justify-center text-shinobi-chakra text-base">
                                        <i class="bi bi-yin-yang"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-serif font-black text-lg uppercase text-white tracking-wide">ÉQUILIBRÉ</h3>
                                        <span class="font-mono text-[10px] text-slate-400">Maîtrise du Chakra</span>
                                    </div>
                                </div>
                                <span id="badge-stance-balanced" class="{{ $initialStance === 'balanced' ? 'inline-block' : 'hidden' }} rounded-full bg-shinobi-chakra px-2.5 py-0.5 font-mono text-[9px] font-bold text-shinobi-dark uppercase shadow">
                                    Active
                                </span>
                            </div>

                            <p class="font-sans text-xs text-slate-300 leading-relaxed mb-4">
                                Offre un parfait équilibre entre puissance, gestion des ressources et adaptabilité face à tout adversaire.
                            </p>

                            <!-- Bonus & Malus Chiffrés -->
                            <div class="space-y-1.5 font-mono text-xs border-t border-shinobi-border/60 pt-3">
                                <div class="flex items-center justify-between text-emerald-400">
                                    <span class="flex items-center gap-1.5"><i class="bi bi-plus-circle"></i> Dégâts globaux</span>
                                    <strong>+10%</strong>
                                </div>
                                <div class="flex items-center justify-between text-emerald-400">
                                    <span class="flex items-center gap-1.5"><i class="bi bi-plus-circle"></i> Réduction des dégâts</span>
                                    <strong>+10%</strong>
                                </div>
                                <div class="flex items-center justify-between text-sky-400">
                                    <span class="flex items-center gap-1.5"><i class="bi bi-droplet-fill"></i> Coût en chakra</span>
                                    <strong>-10%</strong>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-3 border-t border-shinobi-border/40 text-center">
                            <span id="btn-stance-balanced" class="inline-block w-full py-2 rounded-xl font-mono text-xs font-bold transition {{ $initialStance === 'balanced' ? 'bg-shinobi-chakra text-shinobi-dark' : 'bg-white/5 text-slate-300 group-hover:bg-white/10' }}">
                                SÉLECTIONNER
                            </span>
                        </div>
                    </div>
                </label>

                <!-- CARTE 3 : DÉFENSIF -->
                <label class="cursor-pointer group relative block" onclick="selectStance('defensive')">
                    <input type="radio" name="strategy_stance" value="defensive" {{ $initialStance === 'defensive' ? 'checked' : '' }} class="sr-only stance-radio">
                    
                    <div id="card-stance-defensive" class="h-full rounded-2xl border transition-all duration-300 p-5 flex flex-col justify-between bg-shinobi-card/90 shadow-xl {{ $initialStance === 'defensive' ? 'border-emerald-500 bg-gradient-to-b from-emerald-950/40 to-shinobi-card ring-2 ring-emerald-500/40' : 'border-shinobi-border hover:border-emerald-500/50' }}">
                        <div>
                            <!-- Header Posture -->
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400 text-base">
                                        <i class="bi bi-shield-shaded"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-serif font-black text-lg uppercase text-white tracking-wide">DÉFENSIF</h3>
                                        <span class="font-mono text-[10px] text-slate-400">Esquive & Usure</span>
                                    </div>
                                </div>
                                <span id="badge-stance-defensive" class="{{ $initialStance === 'defensive' ? 'inline-block' : 'hidden' }} rounded-full bg-emerald-500 px-2.5 py-0.5 font-mono text-[9px] font-bold text-shinobi-dark uppercase shadow">
                                    Active
                                </span>
                            </div>

                            <p class="font-sans text-xs text-slate-300 leading-relaxed mb-4">
                                Augmente fortement ta résistance aux coups et ton esquive. Conçu pour épuiser les réserves adverses.
                            </p>

                            <!-- Bonus & Malus Chiffrés -->
                            <div class="space-y-1.5 font-mono text-xs border-t border-shinobi-border/60 pt-3">
                                <div class="flex items-center justify-between text-emerald-400">
                                    <span class="flex items-center gap-1.5"><i class="bi bi-plus-circle"></i> Défense & absorption</span>
                                    <strong>+40%</strong>
                                </div>
                                <div class="flex items-center justify-between text-emerald-400">
                                    <span class="flex items-center gap-1.5"><i class="bi bi-plus-circle"></i> Bonus d'esquive</span>
                                    <strong>+20%</strong>
                                </div>
                                <div class="flex items-center justify-between text-rose-400">
                                    <span class="flex items-center gap-1.5"><i class="bi bi-dash-circle"></i> Dégâts bruts</span>
                                    <strong>-20%</strong>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-3 border-t border-shinobi-border/40 text-center">
                            <span id="btn-stance-defensive" class="inline-block w-full py-2 rounded-xl font-mono text-xs font-bold transition {{ $initialStance === 'defensive' ? 'bg-emerald-500 text-shinobi-dark' : 'bg-white/5 text-slate-300 group-hover:bg-white/10' }}">
                                SÉLECTIONNER
                            </span>
                        </div>
                    </div>
                </label>

            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- SECTION 2 : PRIORITÉ DE DÉCLENCHEMENT DES JUTSUS                           -->
        <!-- ========================================================================= -->
        <div class="space-y-4">
            <div>
                <span class="font-mono text-[11px] font-bold uppercase tracking-wider text-shinobi-gold">2. PRIORITÉ DE DÉCLENCHEMENT DES JUTSUS</span>
                <p class="font-mono text-xs text-slate-400 mt-0.5">
                    Définis dans quel ordre tes jutsus seront utilisés en combat selon la situation.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                
                <!-- Option 1 : Plus puissant d'abord -->
                <label class="cursor-pointer group block" onclick="selectPriority('highest_power')">
                    <input type="radio" name="jutsu_priority_type" value="highest_power" {{ $initialPriority === 'highest_power' ? 'checked' : '' }} class="sr-only priority-radio">
                    <div id="card-priority-highest_power" class="h-full rounded-xl border p-4 transition-all relative {{ $initialPriority === 'highest_power' ? 'border-shinobi-red bg-red-950/30 ring-2 ring-shinobi-red/50' : 'border-shinobi-border bg-shinobi-card/80 group-hover:border-slate-500' }}">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-fire text-shinobi-red text-base"></i>
                                <h4 class="font-serif font-bold text-xs uppercase text-white">Plus puissant</h4>
                            </div>
                            <span id="badge-priority-highest_power" class="{{ $initialPriority === 'highest_power' ? 'inline-block' : 'hidden' }} rounded bg-shinobi-red px-1.5 py-0.2 font-mono text-[9px] font-bold text-white uppercase">
                                Active
                            </span>
                        </div>
                        <p class="font-mono text-[11px] text-slate-400 leading-tight">
                            Privilégie les jutsus qui infligent les plus gros dégâts immédiats.
                        </p>
                    </div>
                </label>

                <!-- Option 2 : Faible coût en chakra -->
                <label class="cursor-pointer group block" onclick="selectPriority('lowest_chakra')">
                    <input type="radio" name="jutsu_priority_type" value="lowest_chakra" {{ $initialPriority === 'lowest_chakra' ? 'checked' : '' }} class="sr-only priority-radio">
                    <div id="card-priority-lowest_chakra" class="h-full rounded-xl border p-4 transition-all relative {{ $initialPriority === 'lowest_chakra' ? 'border-shinobi-chakra bg-cyan-950/30 ring-2 ring-shinobi-chakra/50' : 'border-shinobi-border bg-shinobi-card/80 group-hover:border-slate-500' }}">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-droplet-fill text-shinobi-chakra text-base"></i>
                                <h4 class="font-serif font-bold text-xs uppercase text-white">Économie Chakra</h4>
                            </div>
                            <span id="badge-priority-lowest_chakra" class="{{ $initialPriority === 'lowest_chakra' ? 'inline-block' : 'hidden' }} rounded bg-shinobi-chakra px-1.5 py-0.2 font-mono text-[9px] font-bold text-shinobi-dark uppercase">
                                Active
                            </span>
                        </div>
                        <p class="font-mono text-[11px] text-slate-400 leading-tight">
                            Utilise d'abord les jutsus les moins coûteux pour durer dans le temps.
                        </p>
                    </div>
                </label>

                <!-- Option 3 : Avantage élémentaire -->
                <label class="cursor-pointer group block" onclick="selectPriority('element_counter')">
                    <input type="radio" name="jutsu_priority_type" value="element_counter" {{ $initialPriority === 'element_counter' ? 'checked' : '' }} class="sr-only priority-radio">
                    <div id="card-priority-element_counter" class="h-full rounded-xl border p-4 transition-all relative {{ $initialPriority === 'element_counter' ? 'border-emerald-500 bg-emerald-950/30 ring-2 ring-emerald-500/50' : 'border-shinobi-border bg-shinobi-card/80 group-hover:border-slate-500' }}">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-diagram-3 text-emerald-400 text-base"></i>
                                <h4 class="font-serif font-bold text-xs uppercase text-white">Avantage Élément</h4>
                            </div>
                            <span id="badge-priority-element_counter" class="{{ $initialPriority === 'element_counter' ? 'inline-block' : 'hidden' }} rounded bg-emerald-500 px-1.5 py-0.2 font-mono text-[9px] font-bold text-shinobi-dark uppercase">
                                Active
                            </span>
                        </div>
                        <p class="font-mono text-[11px] text-slate-400 leading-tight">
                            Exploite la faiblesse élémentaire de ton adversaire (+25% dégâts).
                        </p>
                    </div>
                </label>

                <!-- Option 4 : Réserve sous 30% PV -->
                <label class="cursor-pointer group block" onclick="selectPriority('taijutsu')">
                    <input type="radio" name="jutsu_priority_type" value="taijutsu" {{ in_array($initialPriority, ['taijutsu', 'ninjutsu', 'genjutsu']) ? 'checked' : '' }} class="sr-only priority-radio">
                    <div id="card-priority-taijutsu" class="h-full rounded-xl border p-4 transition-all relative {{ in_array($initialPriority, ['taijutsu', 'ninjutsu', 'genjutsu']) ? 'border-purple-500 bg-purple-950/30 ring-2 ring-purple-500/50' : 'border-shinobi-border bg-shinobi-card/80 group-hover:border-slate-500' }}">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-heart-pulse-fill text-purple-400 text-base"></i>
                                <h4 class="font-serif font-bold text-xs uppercase text-white">Réserve PV</h4>
                            </div>
                            <span id="badge-priority-taijutsu" class="{{ in_array($initialPriority, ['taijutsu', 'ninjutsu', 'genjutsu']) ? 'inline-block' : 'hidden' }} rounded bg-purple-500 px-1.5 py-0.2 font-mono text-[9px] font-bold text-white uppercase">
                                Active
                            </span>
                        </div>
                        <p class="font-mono text-[11px] text-slate-400 leading-tight">
                            Garde tes meilleures techniques pour les moments critiques sous 30% PV.
                        </p>
                    </div>
                </label>

            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- SECTION 3 : RADAR SVG TACTIQUE DYNAMIQUE & CONSEIL DU SENSEI              -->
        <!-- ========================================================================= -->
        <div class="space-y-4">
            <div>
                <span class="font-mono text-[11px] font-bold uppercase tracking-wider text-slate-400">3. ARÈNE DE SIMULATION / APERÇU TACTIQUE</span>
                <p class="font-mono text-xs text-slate-500 mt-0.5">
                    Modélisation géométrique réelle basée sur les 8 notes du Databook de {{ $fighter->name }}.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
                
                <!-- Radar Tactique SVG 100% Dynamique (lg:col-span-6) -->
                <div class="lg:col-span-6 glass3d rounded-2xl border border-shinobi-border/70 p-5 flex flex-col justify-between">
                    <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-2 mb-3">
                        <span class="font-mono text-xs font-bold text-white uppercase">Radar de Combat Réel</span>
                        <div class="flex items-center gap-3 font-mono text-[10px]">
                            <span class="flex items-center gap-1 text-white">
                                <span id="radar-legend-dot" class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $currentProfile['color'] }};"></span> 
                                Posture active
                            </span>
                            <span class="flex items-center gap-1 text-slate-500">
                                <span class="w-2.5 h-2.5 rounded-full bg-slate-600"></span> 
                                Neutre
                            </span>
                        </div>
                    </div>

                    <!-- Polygone SVG Radar Dynamique réactif en temps réel -->
                    <div class="py-2 flex items-center justify-center">
                        <svg class="w-full max-w-[340px] h-auto select-none overflow-visible" viewBox="0 0 320 300">
                            <!-- Cercles / Niveaux concentriques du radar (20%, 40%, 60%, 80%, 100%) - Centre (160, 150) -->
                            <polygon points="160,60 246,122 213,223 107,223 74,122" fill="none" stroke="#2D2933" stroke-width="1"></polygon>
                            <polygon points="160,78 228,128 202,208 118,208 92,128" fill="none" stroke="#2D2933" stroke-width="1"></polygon>
                            <polygon points="160,96 211,133 192,194 128,194 109,133" fill="none" stroke="#2D2933" stroke-width="1"></polygon>
                            <polygon points="160,114 194,139 181,179 139,179 126,139" fill="none" stroke="#2D2933" stroke-width="1"></polygon>
                            <polygon points="160,132 177,144 171,165 149,165 143,144" fill="none" stroke="#2D2933" stroke-width="1"></polygon>
                            
                            <!-- Axes transversaux depuis le centre (160, 150) -->
                            <line x1="160" y1="150" x2="160" y2="60" stroke="#2D2933" stroke-width="1"></line>
                            <line x1="160" y1="150" x2="246" y2="122" stroke="#2D2933" stroke-width="1"></line>
                            <line x1="160" y1="150" x2="213" y2="223" stroke="#2D2933" stroke-width="1"></line>
                            <line x1="160" y1="150" x2="107" y2="223" stroke="#2D2933" stroke-width="1"></line>
                            <line x1="160" y1="150" x2="74" y2="122" stroke="#2D2933" stroke-width="1"></line>

                            <!-- Polygone Neutre (Calculé depuis les stats brutes) -->
                            <polygon id="radar-neutral-poly" points="{{ $currentProfile['neutral_svg_points'] }}" fill="rgba(255,255,255,0.03)" stroke="#666" stroke-width="1.5" stroke-dasharray="3,3" class="transition-all duration-500"></polygon>
                            
                            <!-- Polygone Actif Réel (Calculé avec les modificateurs de posture) -->
                            <polygon id="radar-active-poly" points="{{ $currentProfile['active_svg_points'] }}" 
                                     fill="{{ $currentProfile['color_bg'] }}" 
                                     stroke="{{ $currentProfile['color'] }}" 
                                     stroke-width="2.5" 
                                     class="transition-all duration-500"></polygon>
                            
                            <!-- Sommets interactifs du polygone actif -->
                            <g id="radar-active-circles">
                                @foreach($currentProfile['active_circles'] as $circle)
                                    <circle cx="{{ $circle['cx'] }}" cy="{{ $circle['cy'] }}" r="4" fill="{{ $currentProfile['color'] }}" class="transition-all duration-500"></circle>
                                @endforeach
                            </g>

                            <!-- Labels vectoriels intégrés avec coordonnées absolues distinctes -->
                            <!-- Sommet 1 : Initiative (Haut / Centre) -->
                            <text id="label-metric-initiative" x="160" y="42" text-anchor="middle" fill="#FFFFFF" class="font-mono text-[11px] font-bold">
                                Initiative ({{ $currentProfile['metrics']['initiative'] }})
                            </text>

                            <!-- Sommet 2 : Dégâts (Haut Droite) -->
                            <text id="label-metric-damage" x="254" y="118" text-anchor="start" fill="{{ $currentProfile['color'] }}" class="font-mono text-[11px] font-bold transition-colors">
                                Dégâts ({{ $currentProfile['metrics']['damage_label'] }})
                            </text>

                            <!-- Sommet 3 : Critique (Bas Droite) -->
                            <text id="label-metric-crit" x="222" y="244" text-anchor="start" fill="#F4CA30" class="font-mono text-[11px] font-bold">
                                Critique ({{ $currentProfile['metrics']['crit'] }}%)
                            </text>

                            <!-- Sommet 4 : Défense (Bas Gauche) -->
                            <text id="label-metric-defense" x="98" y="244" text-anchor="end" fill="#10B981" class="font-mono text-[11px] font-bold">
                                Défense ({{ $currentProfile['metrics']['defense'] }})
                            </text>

                            <!-- Sommet 5 : Esquive (Haut Gauche) -->
                            <text id="label-metric-dodge" x="66" y="118" text-anchor="end" fill="{{ str_starts_with($currentProfile['metrics']['dodge_label'], '+') ? '#10B981' : (str_starts_with($currentProfile['metrics']['dodge_label'], '-') ? '#FB7185' : '#94A3B8') }}" class="font-mono text-[11px] font-bold transition-colors">
                                Esquive ({{ $currentProfile['metrics']['dodge_label'] }})
                            </text>
                        </svg>
                    </div>

                    <div class="mt-2 text-center font-mono text-[10px] text-slate-500">
                        Calculs mathématiques purs indexés sur la Vitesse ({{ $fighter->stat_spd }}), Force ({{ $fighter->stat_str }}), Endurance ({{ $fighter->stat_end }}) et Ninjutsu ({{ $fighter->stat_nin }}).
                    </div>
                </div>

                <!-- Conseil du Sensei Parchemin 100% Dynamique (lg:col-span-6) -->
                <div class="lg:col-span-6 rounded-2xl border border-[#DFC4B5] bg-[#F5E0D7] p-6 text-[#181310] shadow-xl flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 border-b border-[#DEC1B0] pb-2 mb-3">
                            <span class="text-shinobi-red font-serif text-lg font-black">巻</span>
                            <h3 class="font-serif font-black uppercase text-base tracking-wider text-[#120F0D]">
                                Conseil Stratégique du Sensei
                            </h3>
                        </div>

                        <!-- Analyse textuelle adaptée au Clan, à l'Élément et à la Posture -->
                        <p id="sensei-text" class="font-sans text-xs sm:text-sm text-[#3E2B20] leading-relaxed transition-all duration-300">
                            {{ $currentProfile['advice']['text'] }}
                        </p>

                        <!-- Diagnostics de Synergie Réels -->
                        <div class="mt-4 p-3 rounded-xl bg-white/70 border border-[#DEC1B0] font-mono text-xs text-[#2B1B12] space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span>Synergie Clan {{ $fighter->clan }} :</span>
                                <strong id="sensei-synergy" class="text-shinobi-red font-bold">{{ $currentProfile['advice']['synergy'] }}</strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Consommation de Chakra :</span>
                                <strong id="sensei-chakra" class="text-amber-800 font-bold">{{ $currentProfile['advice']['chakra_consumption'] }}</strong>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-[#5A4537] pt-1 border-t border-[#DEC1B0]/50">
                                <span>Affinité Chakra dominante :</span>
                                <strong class="text-[#1A120C] font-semibold">{{ $fighter->primary_element }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton de Soumission Imposant Rouge -->
                    <div class="mt-6 pt-4 border-t border-[#DEC1B0]">
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-3 rounded-xl bg-shinobi-red px-6 py-4 font-mono text-sm font-bold text-white shadow-xl shadow-shinobi-red/30 hover:bg-shinobi-red-hover hover:scale-[1.01] active:scale-[0.99] transition">
                            <i class="bi bi-floppy-fill text-base"></i>
                            <span>ENREGISTRER MA STRATÉGIE DE COMBAT</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </form>

</div>

<script>
    // Stratégies Profiles précalculés côté serveur
    const strategicProfiles = @json($profiles);
    let selectedCurrentStance = '{{ $initialStance }}';
    let selectedCurrentPriority = '{{ $initialPriority }}';

    function selectStance(stance) {
        selectedCurrentStance = stance;

        // Radios
        document.querySelectorAll('.stance-radio').forEach(r => {
            r.checked = (r.value === stance);
        });

        // 1. Update Posture Cards UI
        const stances = ['aggressive', 'balanced', 'defensive'];
        stances.forEach(s => {
            const card = document.getElementById('card-stance-' + s);
            const badge = document.getElementById('badge-stance-' + s);
            const btn = document.getElementById('btn-stance-' + s);

            if (s === stance) {
                badge.classList.remove('hidden');
                badge.classList.add('inline-block');

                if (s === 'aggressive') {
                    card.className = 'h-full rounded-2xl border transition-all duration-300 p-5 flex flex-col justify-between bg-shinobi-card/90 shadow-xl border-shinobi-red bg-gradient-to-b from-red-950/40 to-shinobi-card ring-2 ring-shinobi-red/40';
                    btn.className = 'inline-block w-full py-2 rounded-xl font-mono text-xs font-bold transition bg-shinobi-red text-white';
                } else if (s === 'balanced') {
                    card.className = 'h-full rounded-2xl border transition-all duration-300 p-5 flex flex-col justify-between bg-shinobi-card/90 shadow-xl border-shinobi-chakra bg-gradient-to-b from-cyan-950/40 to-shinobi-card ring-2 ring-shinobi-chakra/40';
                    btn.className = 'inline-block w-full py-2 rounded-xl font-mono text-xs font-bold transition bg-shinobi-chakra text-shinobi-dark';
                } else if (s === 'defensive') {
                    card.className = 'h-full rounded-2xl border transition-all duration-300 p-5 flex flex-col justify-between bg-shinobi-card/90 shadow-xl border-emerald-500 bg-gradient-to-b from-emerald-950/40 to-shinobi-card ring-2 ring-emerald-500/40';
                    btn.className = 'inline-block w-full py-2 rounded-xl font-mono text-xs font-bold transition bg-emerald-500 text-shinobi-dark';
                }
            } else {
                badge.classList.remove('inline-block');
                badge.classList.add('hidden');
                card.className = 'h-full rounded-2xl border transition-all duration-300 p-5 flex flex-col justify-between bg-shinobi-card/90 shadow-xl border-shinobi-border hover:border-slate-500';
                btn.className = 'inline-block w-full py-2 rounded-xl font-mono text-xs font-bold transition bg-white/5 text-slate-300 group-hover:bg-white/10';
            }
        });

        // 2. Update Radar and Sensei
        updateTacticalDisplay();
    }

    function selectPriority(priority) {
        selectedCurrentPriority = priority;

        // Radios
        document.querySelectorAll('.priority-radio').forEach(r => {
            r.checked = (r.value === priority);
        });

        const priorities = ['highest_power', 'lowest_chakra', 'element_counter', 'taijutsu'];
        priorities.forEach(p => {
            const card = document.getElementById('card-priority-' + p);
            const badge = document.getElementById('badge-priority-' + p);

            if (p === priority) {
                badge.classList.remove('hidden');
                badge.classList.add('inline-block');
                if (p === 'highest_power') card.className = 'h-full rounded-xl border p-4 transition-all relative border-shinobi-red bg-red-950/30 ring-2 ring-shinobi-red/50';
                if (p === 'lowest_chakra') card.className = 'h-full rounded-xl border p-4 transition-all relative border-shinobi-chakra bg-cyan-950/30 ring-2 ring-shinobi-chakra/50';
                if (p === 'element_counter') card.className = 'h-full rounded-xl border p-4 transition-all relative border-emerald-500 bg-emerald-950/30 ring-2 ring-emerald-500/50';
                if (p === 'taijutsu') card.className = 'h-full rounded-xl border p-4 transition-all relative border-purple-500 bg-purple-950/30 ring-2 ring-purple-500/50';
            } else {
                badge.classList.remove('inline-block');
                badge.classList.add('hidden');
                card.className = 'h-full rounded-xl border p-4 transition-all relative border-shinobi-border bg-shinobi-card/80 group-hover:border-slate-500';
            }
        });

        // Update chakra consumption estimation in Sensei box
        const chakraElem = document.getElementById('sensei-chakra');
        if (chakraElem) {
            if (priority === 'lowest_chakra') chakraElem.innerText = 'Économique';
            else if (priority === 'highest_power') chakraElem.innerText = (selectedCurrentStance === 'aggressive' ? 'Élevée' : 'Importante');
            else if (priority === 'element_counter') chakraElem.innerText = 'Modérée & Ciblée';
            else chakraElem.innerText = 'Modérée';
        }
    }

    function updateTacticalDisplay() {
        const profile = strategicProfiles[selectedCurrentStance] || strategicProfiles['balanced'];
        if (!profile) return;

        // Radar polygon & circles
        const activePoly = document.getElementById('radar-active-poly');
        if (activePoly) {
            activePoly.setAttribute('points', profile.active_svg_points);
            activePoly.setAttribute('fill', profile.color_bg);
            activePoly.setAttribute('stroke', profile.color);
        }

        const neutralPoly = document.getElementById('radar-neutral-poly');
        if (neutralPoly) {
            neutralPoly.setAttribute('points', profile.neutral_svg_points);
        }

        const legendDot = document.getElementById('radar-legend-dot');
        if (legendDot) {
            legendDot.style.backgroundColor = profile.color;
        }

        const circlesGroup = document.getElementById('radar-active-circles');
        if (circlesGroup && profile.active_circles) {
            circlesGroup.innerHTML = profile.active_circles.map(c => 
                `<circle cx="${c.cx}" cy="${c.cy}" r="4" fill="${profile.color}" class="transition-all duration-500"></circle>`
            ).join('');
        }

        // Radar vector text labels
        const initLabel = document.getElementById('label-metric-initiative');
        if (initLabel) initLabel.textContent = `Initiative (${profile.metrics.initiative})`;

        const dmgLabel = document.getElementById('label-metric-damage');
        if (dmgLabel) {
            dmgLabel.textContent = `Dégâts (${profile.metrics.damage_label})`;
            dmgLabel.setAttribute('fill', profile.color);
        }

        const critLabel = document.getElementById('label-metric-crit');
        if (critLabel) critLabel.textContent = `Critique (${profile.metrics.crit}%)`;

        const defLabel = document.getElementById('label-metric-defense');
        if (defLabel) defLabel.textContent = `Défense (${profile.metrics.defense})`;

        const dodgeLabel = document.getElementById('label-metric-dodge');
        if (dodgeLabel) {
            dodgeLabel.textContent = `Esquive (${profile.metrics.dodge_label})`;
            const dodgeColor = profile.metrics.dodge_label.startsWith('+') ? '#10B981' : (profile.metrics.dodge_label.startsWith('-') ? '#FB7185' : '#94A3B8');
            dodgeLabel.setAttribute('fill', dodgeColor);
        }

        // Sensei advice
        const senseiText = document.getElementById('sensei-text');
        if (senseiText) senseiText.innerText = profile.advice.text;

        const senseiSynergy = document.getElementById('sensei-synergy');
        if (senseiSynergy) senseiSynergy.innerText = profile.advice.synergy;

        const senseiChakra = document.getElementById('sensei-chakra');
        if (senseiChakra) {
            if (selectedCurrentPriority === 'lowest_chakra') senseiChakra.innerText = 'Économique';
            else if (selectedCurrentPriority === 'highest_power') senseiChakra.innerText = (selectedCurrentStance === 'aggressive' ? 'Élevée' : 'Importante');
            else if (selectedCurrentPriority === 'element_counter') senseiChakra.innerText = 'Modérée & Ciblée';
            else senseiChakra.innerText = profile.advice.chakra_consumption;
        }
    }
</script>
@endsection
