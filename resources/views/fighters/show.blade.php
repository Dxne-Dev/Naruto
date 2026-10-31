@extends('layouts.app')

@section('title', $fighter->name . ' — QG Shinobi')

@section('content')
<div class="space-y-6" x-data="{ openShopModal: false }">

    @php
        $maxHp = $fighter->getMaxHp();
        $currentHp = $fighter->current_hp ?? $maxHp;
        $maxChakra = $fighter->getMaxChakra();
        $currentChakra = $fighter->current_chakra ?? $maxChakra;
        $hpPercentage = $maxHp > 0 ? min(100, max(0, round(($currentHp / $maxHp) * 100))) : 100;
        $chakraPercentage = $maxChakra > 0 ? min(100, max(0, round(($currentChakra / $maxChakra) * 100))) : 100;
        $xpTarget = $fighter->level * 1000;
        $xpPercentage = $xpTarget > 0 ? min(100, max(0, round(($fighter->xp / $xpTarget) * 100))) : 0;
        $hasPaidPass = (bool) ($fighter->user?->has_paid_microtransaction);

        // Noms japonais et kanjis des 5 grands villages
        $villageJapaneseMap = [
            'Konoha' => ['full' => 'Konohagakure no Sato', 'kanji' => '木ノ葉'],
            'Suna' => ['full' => 'Sunagakure no Sato', 'kanji' => '砂'],
            'Kiri' => ['full' => 'Kirigakure no Sato', 'kanji' => '霧'],
            'Kumo' => ['full' => 'Kumogakure no Sato', 'kanji' => '雲'],
            'Iwa' => ['full' => 'Iwagakure no Sato', 'kanji' => '岩'],
        ];
        $villageInfo = $villageJapaneseMap[$fighter->village] ?? ['full' => $fighter->village . ' no Sato', 'kanji' => '忍'];

        // Map éléments vers classes de couleurs Tailwind
        $elementStyles = [
            'Katon' => ['border' => 'border-amber-600/70', 'bg' => 'bg-amber-950/20', 'text' => 'text-amber-500', 'badge' => 'bg-amber-600/20 text-amber-400 border-amber-600/40', 'icon' => 'bi-fire'],
            'Suiton' => ['border' => 'border-sky-500/70', 'bg' => 'bg-sky-950/20', 'text' => 'text-sky-400', 'badge' => 'bg-sky-500/20 text-sky-400 border-sky-500/40', 'icon' => 'bi-droplet-fill'],
            'Raiton' => ['border' => 'border-violet-500/70', 'bg' => 'bg-violet-950/20', 'text' => 'text-violet-400', 'badge' => 'bg-violet-500/20 text-violet-400 border-violet-500/40', 'icon' => 'bi-lightning-charge-fill'],
            'Fūton' => ['border' => 'border-emerald-500/70', 'bg' => 'bg-emerald-950/20', 'text' => 'text-emerald-400', 'badge' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40', 'icon' => 'bi-wind'],
            'Doton' => ['border' => 'border-yellow-700/70', 'bg' => 'bg-yellow-950/20', 'text' => 'text-yellow-600', 'badge' => 'bg-yellow-700/20 text-yellow-500 border-yellow-700/40', 'icon' => 'bi-shield-shaded'],
        ];
    @endphp

    <!-- ========================================================================= -->
    <!-- NOUVELLE ARCHITECTURE DU DASHBOARD : CARTE SHINOBI EN PREMIER             -->
    <!-- ========================================================================= -->

    <!-- ========================================================================= -->
    <!-- 1. BLOC MAÎTRE : CARTE SHŌNEN JUMP & DATABOOK OFFICIEL DU NINJA          -->
    <!-- ========================================================================= -->
    <section class="rounded-3xl border border-shinobi-border/80 bg-shinobi-card p-4 sm:p-5 shadow-2xl relative overflow-hidden space-y-4">
        
        <!-- Glow d'ambiance de chakra -->
        <div class="pointer-events-none absolute -top-12 -right-12 w-64 h-64 bg-shinobi-red/15 rounded-full blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-12 -left-12 w-64 h-64 bg-shinobi-chakra/10 rounded-full blur-3xl"></div>

        <!-- En-tête Supérieur de la Carte : Identité Ninja & Village Officiel -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-shinobi-border/60 pb-3">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Clan -->
                    <span class="font-mono text-[10px] font-black uppercase tracking-wider text-shinobi-red px-2 py-0.5 rounded bg-shinobi-red/10 border border-shinobi-red/30">
                        CLAN {{ strtoupper($fighter->clan) }}
                    </span>
                    
                    <!-- Village d'origine avec Kanji -->
                    <span class="rounded bg-white/5 border border-shinobi-border px-2.5 py-0.5 font-mono text-[11px] text-slate-300 flex items-center gap-1.5" title="{{ $villageInfo['full'] }}">
                        <span class="font-serif font-black text-xs text-shinobi-gold">{{ $villageInfo['kanji'] }}</span>
                        <span>{{ $villageInfo['full'] }}</span>
                    </span>

                    <!-- Rang Ninja -->
                    <span class="rounded bg-shinobi-gold/15 border border-shinobi-gold/40 px-2.5 py-0.5 font-mono text-[10px] font-bold text-shinobi-gold">
                        {{ $fighter->ninja_rank }}
                    </span>
                </div>

                <!-- Nom du Ninja & Titre/Spécialisation -->
                <div class="flex flex-wrap items-baseline gap-2.5">
                    <h1 class="font-serif text-2xl sm:text-3xl font-black uppercase text-white tracking-wide">
                        {{ $fighter->name }}
                    </h1>
                    <span class="font-mono text-[11px] text-shinobi-gold bg-shinobi-gold/10 px-2 py-0.5 rounded border border-shinobi-gold/30">
                        {{ $fighter->specialization_label }}
                    </span>
                </div>
            </div>

            <!-- Niveau & Affinités Élémentaires Directes -->
            <div class="flex md:flex-col items-center md:items-end justify-between gap-1.5 border-t md:border-t-0 pt-2 md:pt-0 border-shinobi-border/40 font-mono">
                <div class="text-left md:text-right">
                    <span class="text-slate-400 block text-[9px] uppercase tracking-wider">Rang & Maîtrise</span>
                    <span class="font-serif font-black text-xl text-white">Niveau {{ $fighter->level }}</span>
                </div>
                
                <!-- Badges d'Affinités Élémentaires (Primaire + Secondaire) -->
                <div class="flex items-center gap-1.5 mt-0.5">
                    @php
                        $pStyle = $elementStyles[$fighter->primary_element] ?? ['badge' => 'bg-white/10 text-slate-300 border-white/20', 'icon' => 'bi-stars'];
                    @endphp
                    <span class="rounded {{ $pStyle['badge'] }} border px-2 py-0.5 text-[10px] font-bold flex items-center gap-1" title="Affinité Élémentaire Principale">
                        <i class="bi {{ $pStyle['icon'] }}"></i>
                        <span>{{ $fighter->primary_element }}</span>
                    </span>

                    @if($fighter->secondary_element)
                        @php
                            $sStyle = $elementStyles[$fighter->secondary_element] ?? ['badge' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40', 'icon' => 'bi-sparkles'];
                        @endphp
                        <span class="rounded {{ $sStyle['badge'] }} border px-2 py-0.5 text-[10px] font-bold flex items-center gap-1" title="Affinité Élémentaire Secondaire">
                            <i class="bi {{ $sStyle['icon'] }}"></i>
                            <span>{{ $fighter->secondary_element }}</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Corps de la Carte : Portrait Shōnen Jump (Paywall Blur) + Databook Officiel 8 Stats -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-stretch">
            
            <!-- Colonne Gauche : Portrait Shōnen Jump & Jauges Vitales (md:col-span-5) -->
            <div class="md:col-span-5 flex flex-col justify-between space-y-3">
                
                <!-- Carte Portrait Avatar -->
                <div class="relative rounded-2xl overflow-hidden border border-shinobi-border/80 aspect-4/5 bg-black/90 shadow-2xl group">
                    
                    <!-- Avatar réel en arrière-plan -->
                    @php
                        $portraitReady = $fighter->portrait_status === 'ready' && !empty($fighter->portrait_path);
                        $portraitPending = $hasPaidPass && !$portraitReady;
                    @endphp

                    <img src="{{ $fighter->display_avatar_url }}" 
                         alt="{{ $fighter->name }}" 
                         class="w-full h-full object-cover object-top {{ (!$hasPaidPass || $portraitPending) ? 'filter blur-2xl scale-110 opacity-30 brightness-50' : 'group-hover:scale-105' }} transition-all duration-700"
                         onerror="this.onerror=null;this.src='{{ asset('images/naruto-hero.png') }}';">
                    
                    <!-- Masquage Premier Plan : Image Cadenas Locked officielle si non payé -->
                    @if(!$hasPaidPass)
                        <!-- Image Locked couvrant l'intégralité du cadre -->
                        <div class="absolute inset-0 z-10 select-none">
                            <img src="{{ asset('images/locked-avatar.png') }}" 
                                 alt="Avatar Verrouillé" 
                                 class="w-full h-full object-cover object-center pointer-events-none">
                            
                            <!-- Action et bouton intégrés directement sur l'image du cadenas -->
                            <div class="absolute bottom-3 left-3 right-3 space-y-2">
                                <p class="font-mono text-[11px] text-slate-200 text-center leading-tight drop-shadow-[0_2px_4px_rgba(0,0,0,0.9)]">
                                    Envie de découvrir le portrait de ton personnage ?
                                </p>
                                <a href="{{ route('payment.checkout') }}" class="w-full rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white py-2.5 font-mono text-xs font-black uppercase tracking-wide shadow-xl shadow-shinobi-red/50 transition flex items-center justify-center gap-2 cursor-pointer">
                                    <span>DÉBLOQUER MON PERSONNAGE</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    @elseif($portraitPending)
                        <!-- Masquage Pass Payé mais Portrait en Attente de Forge par l'Admin : Image Mission Passed + Timer Dynamique de 30 min -->
                        <div class="absolute inset-0 z-10 select-none flex flex-col justify-between p-3.5 bg-black/60 backdrop-blur-sm"
                             x-data="{
                                orderedAt: {{ $fighter->portrait_ordered_at ? $fighter->portrait_ordered_at->timestamp * 1000 : 'Date.now()' }},
                                totalMs: 30 * 60 * 1000,
                                remainingText: '30:00',
                                init() {
                                    const updateTimer = () => {
                                        const elapsed = Date.now() - this.orderedAt;
                                        const remaining = Math.max(0, this.totalMs - elapsed);
                                        const mins = Math.floor(remaining / 60000);
                                        const secs = Math.floor((remaining % 60000) / 1000);
                                        this.remainingText = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                                    };
                                    updateTimer();
                                    setInterval(updateTimer, 1000);
                                }
                             }">
                            
                            <!-- Badge Statut en haut -->
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-shinobi-gold/20 border border-shinobi-gold/50 text-shinobi-gold text-[10px] font-mono font-bold tracking-wider uppercase">
                                    <span class="w-1.5 h-1.5 rounded-full bg-shinobi-gold animate-ping"></span>
                                    Forge en cours
                                </span>
                                <div class="px-2.5 py-1 rounded-full bg-black/80 border border-white/20 text-white font-mono text-[11px] font-black flex items-center gap-1 shadow">
                                    <i class="bi bi-clock-history text-shinobi-gold"></i>
                                    <span x-text="remainingText">30:00</span>
                                </div>
                            </div>

                            <!-- Image Mission Passed Overlay au centre -->
                            <div class="my-auto text-center px-2">
                                <img src="{{ asset('images/mission-passed-overlay.png') }}" 
                                     alt="Mission Validée - Forge en Cours" 
                                     class="w-full max-h-36 object-contain mx-auto drop-shadow-[0_4px_20px_rgba(0,0,0,0.9)] animate-pulse">
                                <h4 class="mt-2 font-serif font-black text-white text-base sm:text-lg uppercase tracking-wider drop-shadow">
                                    Pacte Shinobi Scellé
                                </h4>
                                <p class="text-[11px] font-mono text-slate-300 leading-snug mt-1">
                                    Les Maîtres Artisans forgent actuellement ton portrait officiel HD à partir de ta photo.
                                </p>
                            </div>

                            <!-- Pied de carte en attente -->
                            <div class="bg-shinobi-dark/90 border border-shinobi-gold/30 rounded-xl p-2 text-center font-mono text-[10px] text-shinobi-gold">
                                <i class="bi bi-hourglass-split me-1"></i> Notification automatique dès livraison (~30 min max)
                            </div>
                        </div>
                    @endif

                    <!-- Sceau Kanji discret en filigrane sur le portrait (si débloqué et portrait prêt) -->
                    @if($hasPaidPass && $portraitReady)
                        <div class="absolute top-2 right-3 font-serif text-4xl font-black text-white/10 select-none pointer-events-none">
                            {{ $villageInfo['kanji'] }}
                        </div>

                        <!-- Badge XP sur Avatar si débloqué -->
                        <div class="absolute bottom-2 left-2 right-2 flex items-center justify-between text-xs font-mono">
                            <span class="bg-shinobi-dark/90 border border-shinobi-border/80 px-2.5 py-1 rounded text-[10px] font-bold text-white shadow">
                                {{ $fighter->xp }} / {{ $xpTarget }} XP
                            </span>
                            @if(empty($fighter->source_photo_path))
                                <button type="button" @click="$refs.uploadPortraitModal.classList.remove('hidden')" class="bg-shinobi-gold text-slate-950 font-bold px-2 py-1 rounded text-[10px] shadow flex items-center gap-1 hover:brightness-110">
                                    <i class="bi bi-camera-fill"></i>
                                    <span>Ajouter ma photo</span>
                                </button>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Progression XP & Vitalité -->
                <div class="space-y-3 font-mono text-xs bg-white/5 rounded-2xl border border-shinobi-border/50 p-3.5">
                    
                    <!-- Barre Progression XP -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-[10px] text-slate-400">
                            <span>Progression Niveau</span>
                            <span class="text-shinobi-gold font-bold">{{ $xpPercentage }}%</span>
                        </div>
                        <div class="w-full bg-shinobi-dark rounded-full h-1.5 border border-shinobi-border/60 overflow-hidden">
                            <div class="bg-gradient-to-r from-amber-500 to-shinobi-gold h-full rounded-full transition-all duration-500" style="width: {{ $xpPercentage }}%;"></div>
                        </div>
                    </div>

                    <!-- Jauges PV & Chakra -->
                    <div class="space-y-2 pt-1">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-300 font-bold flex items-center gap-1.5"><i class="bi bi-heart-fill text-shinobi-red"></i> PV</span>
                                <span class="text-white font-bold">{{ $currentHp }} / {{ $maxHp }}</span>
                            </div>
                            <div class="w-full bg-shinobi-dark rounded-full h-2 border border-shinobi-border/70 overflow-hidden">
                                <div class="bg-gradient-to-r from-red-700 to-rose-500 h-full rounded-full" style="width: {{ $hpPercentage }}%;"></div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-300 font-bold flex items-center gap-1.5"><i class="bi bi-droplet-fill text-shinobi-chakra"></i> Chakra</span>
                                <span class="text-white font-bold">{{ $currentChakra }} / {{ $maxChakra }}</span>
                            </div>
                            <div class="w-full bg-shinobi-dark rounded-full h-2 border border-shinobi-border/70 overflow-hidden">
                                <div class="bg-gradient-to-r from-blue-700 to-shinobi-chakra h-full rounded-full" style="width: {{ $chakraPercentage }}%;"></div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Colonne Droite : Fiche Parchemin Databook Officiel (md:col-span-7) -->
            <div class="md:col-span-7 rounded-2xl border border-[#DFC4B5] bg-[#F5E0D7] p-4 sm:p-6 text-[#181310] shadow-xl flex flex-col justify-between relative overflow-hidden">
                
                <!-- En-tête Parchemin Databook -->
                <div class="flex items-center justify-between border-b border-[#DEC1B0] pb-3 mb-3">
                    <div>
                        <span class="font-serif font-black uppercase text-sm sm:text-base tracking-wider text-[#120F0D] block">
                            Fiche Officielle Databook
                        </span>
                        <span class="font-mono text-[10px] text-[#5B463A]">
                            Paramètres d'aptitude shinobi de l'arène
                        </span>
                    </div>
                    <span class="rounded bg-[#2A1810] px-2.5 py-1 font-mono text-[10px] font-bold text-white uppercase shadow">
                        Livre des Données
                    </span>
                </div>

                <!-- Les 8 Statistiques Canoniques -->
                <div class="space-y-2 font-mono text-xs my-auto py-2">
                    
                    @php
                        $statsList = [
                            ['label' => 'Ninjutsu', 'sub' => 'Arts ninja', 'val' => $fighter->stat_nin, 'color' => 'bg-shinobi-red'],
                            ['label' => 'Taijutsu', 'sub' => 'Corps à corps', 'val' => $fighter->stat_tai, 'color' => 'bg-amber-700'],
                            ['label' => 'Genjutsu', 'sub' => 'Illusions', 'val' => $fighter->stat_gen, 'color' => 'bg-purple-700'],
                            ['label' => 'Intelligence', 'sub' => 'Stratégie', 'val' => $fighter->stat_int, 'color' => 'bg-blue-700'],
                            ['label' => 'Force', 'sub' => 'Puissance brute', 'val' => $fighter->stat_str, 'color' => 'bg-amber-800'],
                            ['label' => 'Vitesse', 'sub' => 'Agilité réflexe', 'val' => $fighter->stat_spd, 'color' => 'bg-teal-700'],
                            ['label' => 'Endurance', 'sub' => 'Résistance & souffle', 'val' => $fighter->stat_end, 'color' => 'bg-orange-800'],
                            ['label' => 'Sceaux', 'sub' => 'Maîtrise des mudras', 'val' => $fighter->stat_sea, 'color' => 'bg-rose-800'],
                        ];
                    @endphp

                    @foreach($statsList as $st)
                        <div class="flex items-center justify-between gap-3">
                            <div class="w-28 shrink-0">
                                <span class="text-xs font-bold text-[#15100D] block leading-none">{{ $st['label'] }}</span>
                                <span class="text-[9px] text-[#6A5243]">{{ $st['sub'] }}</span>
                            </div>
                            <div class="flex-grow bg-[#DEC1B0] rounded-full h-2 overflow-hidden shadow-inner">
                                <div class="{{ $st['color'] }} h-full rounded-full" style="width: {{ ($st['val'] / 5.0) * 100 }}%;"></div>
                            </div>
                            <span class="font-bold text-[#15100D] text-xs w-10 text-right">{{ number_format($st['val'], 1) }}</span>
                        </div>
                    @endforeach

                </div>

                <!-- Footer Databook : Total & Nindō -->
                <div class="mt-4 pt-3 border-t border-[#DEC1B0] space-y-3 font-mono">
                    <div class="flex items-center justify-between">
                        <span class="text-[#5B463A] font-bold text-xs uppercase">TOTAL DATABOOK</span>
                        <span class="font-serif font-black text-base text-[#18120D]">{{ number_format($fighter->getTotalDatabookScore(), 1) }} / 40.0</span>
                    </div>

                    <!-- Nindō / Voie du Ninja en bas du parchemin -->
                    <div class="pt-2 border-t border-[#DEC1B0]/60">
                        <span class="text-[9px] uppercase font-bold tracking-widest text-[#6A5243] block">Nindō personnel</span>
                        <p class="font-serif italic text-xs text-[#2A1810] leading-snug mt-0.5">
                            « {{ $fighter->talent ?? "Un vrai ninja ne se bat pas pour la gloire, mais pour ce qu'il protège." }} »
                        </p>
                    </div>
                </div>

            </div>

        </div>

    </section>

    <!-- ========================================================================= -->
    <!-- 2. BARRE D'ACTIONS ET OUTILS RAPIDES                                      -->
    <!-- ========================================================================= -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Action 1 : Salle d'entraînement (Duel rapide IA) -->
        <form method="POST" action="{{ route('battles.training') }}" class="w-full">
            @csrf
            <button type="submit" class="w-full h-full rounded-2xl bg-gradient-to-r from-shinobi-red to-[#B0060F] p-4 text-white shadow-xl shadow-shinobi-red/20 hover:scale-[1.02] active:scale-[0.98] transition flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <i class="bi bi-shield-shaded text-2xl"></i>
                    <div class="text-left">
                        <span class="block font-serif font-bold text-xs uppercase tracking-wide">Entraînement</span>
                        <span class="block font-mono text-[10px] text-white/80">Duel rapide IA</span>
                    </div>
                </div>
                <i class="bi bi-chevron-right text-xs"></i>
            </button>
        </form>

        <!-- Action 2 : Stratégies de Combat (Radar & Postures) -->
        <a href="{{ route('strategies.index') }}" class="rounded-2xl glass3d border border-shinobi-border/70 p-4 text-slate-200 hover:text-white hover:border-shinobi-red/50 transition flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="bi bi-sliders text-2xl text-shinobi-red"></i>
                <div>
                    <span class="block font-serif font-bold text-xs uppercase tracking-wide text-white">Stratégies</span>
                    <span class="block font-mono text-[10px] text-slate-400">Postures & Radar</span>
                </div>
            </div>
            <i class="bi bi-chevron-right text-xs"></i>
        </a>

        <!-- Action 3 : Boutique du Destin Modal Trigger -->
        <button type="button" 
                @click="openShopModal = true"
                class="rounded-2xl glass3d border border-shinobi-border/70 p-4 text-slate-200 hover:text-white hover:border-shinobi-gold/50 transition flex items-center justify-between cursor-pointer text-left">
            <div class="flex items-center gap-3">
                <i class="bi bi-stars text-2xl text-shinobi-gold"></i>
                <div>
                    <span class="block font-serif font-bold text-xs uppercase tracking-wide text-white">Boutique</span>
                    <span class="block font-mono text-[10px] text-slate-400">Altérer le destin</span>
                </div>
            </div>
            <i class="bi bi-chevron-right text-xs"></i>
        </button>

        <!-- Action 4 : Compteur Jetons de Destin -->
        <div class="rounded-2xl glass3d border border-shinobi-border/70 p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-shinobi-gold/15 border border-shinobi-gold/30 flex items-center justify-center text-shinobi-gold shrink-0">
                    <i class="bi bi-coin text-xl"></i>
                </div>
                <div>
                    <span class="font-serif font-black text-lg text-shinobi-gold leading-none block">{{ $wheelTokens }}</span>
                    <span class="font-mono text-[10px] text-slate-400 block">Jeton(s) de Destin</span>
                </div>
            </div>
            <button type="button" 
                    @click="openShopModal = true"
                    class="font-mono text-[10px] text-shinobi-gold hover:underline cursor-pointer">
                Utiliser ▸
            </button>
        </div>

    </section>

    <!-- ========================================================================= -->
    <!-- 3. DEUXIÈME LIGNE : JUTSUS ÉQUIPÉS (2/3) & TOURNOI / HISTORIQUE (1/3)     -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Colonne Jutsus Équipés (8 Cols Large) -->
        <section class="lg:col-span-8 glass3d rounded-2xl border border-shinobi-border/70 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-3">
                <div class="flex items-center gap-2">
                    <i class="bi bi-lightning-charge-fill text-shinobi-gold text-lg"></i>
                    <div>
                        <h2 class="font-serif font-black uppercase text-base text-white tracking-wide">
                            Jutsus Équipés en Combat
                        </h2>
                        <span class="font-mono text-[10px] text-slate-400">Techniques actives chargées en mémoire pour les arènes</span>
                    </div>
                </div>
                <span class="font-mono text-xs rounded bg-white/5 border border-shinobi-border px-2.5 py-1">
                    <strong class="text-shinobi-gold">{{ $fighter->jutsus->count() }}</strong> / 5
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                @forelse($fighter->jutsus as $jutsu)
                    @php
                        $style = $elementStyles[$jutsu->element] ?? ['border' => 'border-shinobi-border', 'bg' => 'bg-white/5', 'text' => 'text-slate-300', 'badge' => 'bg-white/10 text-slate-300 border-white/20', 'icon' => 'bi-stars'];
                    @endphp
                    <div class="rounded-xl border {{ $style['border'] }} {{ $style['bg'] }} p-3 space-y-2 flex flex-col justify-between hover:scale-[1.01] transition">
                        <div class="space-y-1.5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="w-7 h-7 rounded-lg bg-shinobi-dark border {{ $style['border'] }} flex items-center justify-center shrink-0 {{ $style['text'] }}">
                                    <i class="bi {{ $style['icon'] }} text-xs"></i>
                                </div>
                                <span class="rounded {{ $style['badge'] }} border px-1.5 py-0.5 font-mono text-[9px] font-bold shrink-0">
                                    Niv.{{ $jutsu->level ?? 1 }}
                                </span>
                            </div>
                            <div>
                                <h3 class="font-serif font-bold text-xs text-white truncate">{{ $jutsu->name }}</h3>
                                <span class="font-mono text-[10px] text-slate-400 block">{{ $jutsu->type }} • {{ $jutsu->element }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-shinobi-border/40 font-mono text-[10px]">
                            <span class="text-slate-400">Consommation :</span>
                            <strong class="{{ $style['text'] }}">{{ $jutsu->chakra_cost }} PC</strong>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center text-slate-400 font-mono text-xs">
                        <i class="bi bi-slash-circle text-2xl text-slate-500 block mb-2"></i>
                        Aucun jutsu équipé actuellement dans ton grimoire.
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Colonne Widgets Arène : Tournoi & Derniers Combats (4 Cols Large) -->
        <aside class="lg:col-span-4 space-y-4">
            
            <!-- Prochain Tournoi Chūnin (Sobre) -->
            <div class="glass3d rounded-2xl border border-shinobi-border/70 p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-2">
                    <span class="font-mono text-[10px] uppercase font-bold tracking-wider text-shinobi-red">Prochain Tournoi</span>
                    <i class="bi bi-trophy text-shinobi-gold text-xs"></i>
                </div>

                @if(!empty($nextTournament))
                    <div class="space-y-2">
                        <span class="rounded bg-shinobi-red/15 border border-shinobi-red/30 px-2 py-0.5 font-mono text-[9px] font-bold text-shinobi-red uppercase block w-fit">
                            {{ $nextTournament->status_label }}
                        </span>
                        <h3 class="font-serif font-bold text-sm text-white">
                            {{ $nextTournament->title }}
                        </h3>
                        <div class="flex items-center justify-between font-mono text-xs pt-1 border-t border-shinobi-border/40">
                            <span class="text-slate-400">Inscrits :</span>
                            <strong class="text-white">{{ $nextTournament->entries_count }} / 8</strong>
                        </div>
                    </div>

                    <a href="{{ route('tournaments.open-list') }}" class="block w-full text-center rounded-xl bg-white/10 hover:bg-white/15 border border-shinobi-border text-white font-mono text-xs font-semibold py-2 transition">
                        Accéder aux Tournois ▸
                    </a>
                @else
                    <div class="py-4 text-center text-slate-400 font-mono text-xs space-y-1">
                        <i class="bi bi-hourglass text-slate-500 text-lg block"></i>
                        <span>Aucun tournoi en cours</span>
                    </div>
                @endif
            </div>

            <!-- Derniers Combats Réels -->
            <div class="glass3d rounded-2xl border border-shinobi-border/70 p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-2">
                    <span class="font-mono text-[10px] uppercase font-bold tracking-wider text-slate-400">Derniers Combats</span>
                    <a href="{{ route('battles.index') }}" class="font-mono text-[10px] text-shinobi-gold hover:underline">Historique</a>
                </div>

                <div class="space-y-2.5">
                    @forelse($recentBattles ?? [] as $b)
                        @php
                            $isWinner = $b->winner_id === $fighter->id;
                            $opponent = $b->fighter_a_id === $fighter->id ? $b->fighterB : $b->fighterA;
                        @endphp
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-white/5 border border-shinobi-border/50 font-mono text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-shinobi-dark border border-shinobi-border overflow-hidden shrink-0">
                                    <img src="{{ $opponent?->portrait_path ?? asset('images/naruto-hero.png') }}" alt="{{ $opponent?->name }}" class="w-full h-full object-cover">
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-[10px] {{ $isWinner ? 'text-emerald-400' : 'text-shinobi-red' }}">
                                            {{ $isWinner ? 'Victoire' : 'Défaite' }}
                                        </span>
                                        <span class="text-slate-400 text-[10px] truncate">vs {{ $opponent?->name ?? 'Ninja Rival' }}</span>
                                    </div>
                                    <span class="text-[9px] text-slate-500 block">{{ $b->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold {{ $isWinner ? 'text-shinobi-gold' : 'text-slate-500' }}">
                                {{ $isWinner ? '+100 XP' : '-50 XP' }}
                            </span>
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400 font-mono text-xs space-y-2">
                            <i class="bi bi-inbox text-slate-500 text-xl block"></i>
                            <p>Aucun duel enregistré.</p>
                            <span class="text-[10px] text-slate-500 block">Lance un combat pour inscrire tes exploits !</span>
                        </div>
                    @endforelse
                </div>
            </div>

        </aside>

    </div>

    <!-- ========================================================================= -->
    <!-- MODALE POPUP : BOUTIQUE DU DESTIN (ALTÉRATION DU NINJA VIA JETONS)       -->
    <!-- ========================================================================= -->
    <div x-show="openShopModal" 
         x-cloak 
         @keydown.escape.window="openShopModal = false"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-6"
         style="display: none;">
        
        <div class="fixed inset-0" @click="openShopModal = false"></div>

        <div class="relative w-full max-w-2xl rounded-3xl border border-shinobi-border/80 bg-shinobi-card p-6 shadow-2xl space-y-6 z-10 overflow-hidden"
             @click.stop>
            
            <!-- En-tête Modale -->
            <div class="flex items-start justify-between gap-4 border-b border-shinobi-border/60 pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-[10px] font-bold uppercase tracking-wider text-shinobi-gold">TEMPLE DU DESTIN</span>
                        <span class="rounded bg-shinobi-gold/15 border border-shinobi-gold/40 px-2 py-0.5 font-mono text-[10px] font-bold text-shinobi-gold">
                            {{ $wheelTokens }} jeton(s) en réserve
                        </span>
                    </div>
                    <h2 class="font-serif text-2xl font-black uppercase text-white mt-1">
                        Boutique du Destin
                    </h2>
                    <p class="font-mono text-xs text-slate-300">
                        Consomme tes jetons de destin pour altérer les fondements de ton shinobi.
                    </p>
                </div>

                <button type="button" 
                        @click="openShopModal = false"
                        class="w-9 h-9 rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white flex items-center justify-center transition cursor-pointer">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            <!-- Les 3 Rituels du Destin -->
            <div class="space-y-4">
                
                <!-- 1. Éveiller un Élément Secondaire (1 Jeton) -->
                <div class="rounded-2xl border border-shinobi-border bg-white/5 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-shinobi-border/90 transition">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-serif font-bold text-sm text-white">Éveiller / Changer d'Élément Secondaire</span>
                            <span class="rounded bg-sky-500/20 text-sky-400 border border-sky-500/40 px-2 py-0.2 font-mono text-[9px] font-bold">1 Jeton</span>
                        </div>
                        <p class="font-mono text-xs text-slate-400">
                            Tire au sort une nouvelle affinité élémentaire secondaire compatible parmi les éléments majeurs.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('fighters.shop', $fighter) }}" class="shrink-0">
                        @csrf
                        <input type="hidden" name="action" value="reroll_element">
                        <button type="submit" 
                                class="w-full sm:w-auto rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white px-4 py-2 font-mono text-xs font-bold transition {{ $wheelTokens < 1 ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}"
                                {{ $wheelTokens < 1 ? 'disabled' : '' }}>
                            Éveiller (1 Jeton)
                        </button>
                    </form>
                </div>

                <!-- 2. Débloquer un Jutsu Rare Compatible (3 Jetons) -->
                <div class="rounded-2xl border border-shinobi-border bg-white/5 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-shinobi-border/90 transition">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-serif font-bold text-sm text-white">Débloquer un Jutsu Secret Rare</span>
                            <span class="rounded bg-amber-500/20 text-amber-400 border border-amber-500/40 px-2 py-0.2 font-mono text-[9px] font-bold">3 Jetons</span>
                        </div>
                        <p class="font-mono text-xs text-slate-400">
                            Apprends une technique interdite compatible avec tes éléments et ton clan (remplace un slot si complet).
                        </p>
                    </div>

                    <form method="POST" action="{{ route('fighters.shop', $fighter) }}" class="shrink-0">
                        @csrf
                        <input type="hidden" name="action" value="unlock_rare_jutsu">
                        <button type="submit" 
                                class="w-full sm:w-auto rounded-xl bg-shinobi-gold hover:bg-amber-400 text-shinobi-dark px-4 py-2 font-mono text-xs font-bold transition {{ $wheelTokens < 3 ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}"
                                {{ $wheelTokens < 3 ? 'disabled' : '' }}>
                            Débloquer (3 Jetons)
                        </button>
                    </form>
                </div>

                <!-- 3. Transcender la Lignée / Changer de Clan (5 Jetons) -->
                <div class="rounded-2xl border border-shinobi-border bg-white/5 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-shinobi-border/90 transition">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-serif font-bold text-sm text-white">Transcender la Lignée (Changer de Clan)</span>
                            <span class="rounded bg-purple-500/20 text-purple-400 border border-purple-500/40 px-2 py-0.2 font-mono text-[9px] font-bold">5 Jetons</span>
                        </div>
                        <p class="font-mono text-xs text-slate-400">
                            Rejoins un nouveau clan légendaire, adopte son don héréditaire secret et ses techniques de clan.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('fighters.shop', $fighter) }}" class="shrink-0">
                        @csrf
                        <input type="hidden" name="action" value="change_clan">
                        <button type="submit" 
                                class="w-full sm:w-auto rounded-xl border border-purple-500/60 bg-purple-950/40 hover:bg-purple-900/60 text-purple-200 px-4 py-2 font-mono text-xs font-bold transition {{ $wheelTokens < 5 ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}"
                                {{ $wheelTokens < 5 ? 'disabled' : '' }}>
                            Transcender (5 Jetons)
                        </button>
                    </form>
                </div>

            </div>

            <!-- Pied de Modale -->
            <div class="pt-4 border-t border-shinobi-border/60 flex items-center justify-between font-mono text-xs text-slate-400">
                <span>Tu peux acquérir des jetons supplémentaires lors des événements.</span>
                <button type="button" 
                        @click="openShopModal = false"
                        class="rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 px-4 py-2 text-slate-300 hover:text-white transition cursor-pointer">
                    Fermer
                </button>
            </div>
        </div>
    </div>

    <!-- Modale Ajout de Photo pour Portrait HD -->
    <div x-ref="uploadPortraitModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
        <div class="relative w-full max-w-md rounded-3xl border border-shinobi-gold/60 bg-gradient-to-b from-[#1C1822] via-shinobi-card to-[#120F15] p-6 sm:p-8 shadow-2xl text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-shinobi-gold/20 border border-shinobi-gold/50 flex items-center justify-center text-shinobi-gold text-2xl">
                <i class="bi bi-camera-fill"></i>
            </div>
            <h3 class="font-serif text-2xl font-black text-white uppercase">Importer ma Photo</h3>
            <p class="font-sans text-xs text-slate-300 leading-relaxed">
                Téléverse ta photo pour que les Maîtres Artisans forgent ton portrait officiel Shinobi HD dès confirmation de ton Pass.
            </p>
            <form method="POST" action="{{ route('payment.upload-photo') }}" enctype="multipart/form-data" class="space-y-4 pt-2">
                @csrf
                <input type="file" name="source_photo_file" accept="image/*" required class="w-full text-xs font-mono text-slate-300 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-mono file:font-bold file:bg-shinobi-gold file:text-slate-950 hover:file:brightness-110">
                <div class="flex items-center justify-center gap-3 pt-2">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-shinobi-gold hover:brightness-110 text-slate-950 font-mono text-xs font-black uppercase tracking-wider transition">
                        Enregistrer
                    </button>
                    <button type="button" @click="$refs.uploadPortraitModal.classList.add('hidden')" class="px-5 py-2.5 rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-300 font-mono text-xs uppercase transition">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

        </div>
    </div>

</div>
@endsection
