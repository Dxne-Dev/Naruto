@extends('layouts.app')

@section('title', 'Carte Ninja — ' . $fighter->name)

@section('content')
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

    $villageJapaneseMap = [
        'Konoha' => ['full' => 'Konohagakure no Sato', 'kanji' => '木ノ葉'],
        'Suna' => ['full' => 'Sunagakure no Sato', 'kanji' => '砂'],
        'Kiri' => ['full' => 'Kirigakure no Sato', 'kanji' => '霧'],
        'Kumo' => ['full' => 'Kumogakure no Sato', 'kanji' => '雲'],
        'Iwa' => ['full' => 'Iwagakure no Sato', 'kanji' => '岩'],
    ];
    $villageInfo = $villageJapaneseMap[$fighter->village] ?? ['full' => $fighter->village . ' no Sato', 'kanji' => '忍'];

    $elementStyles = [
        'Katon' => ['border' => 'border-amber-600/70', 'bg' => 'bg-amber-950/20', 'text' => 'text-amber-500', 'badge' => 'bg-amber-600/20 text-amber-400 border-amber-600/40', 'icon' => 'bi-fire'],
        'Suiton' => ['border' => 'border-sky-500/70', 'bg' => 'bg-sky-950/20', 'text' => 'text-sky-400', 'badge' => 'bg-sky-500/20 text-sky-400 border-sky-500/40', 'icon' => 'bi-droplet-fill'],
        'Raiton' => ['border' => 'border-violet-500/70', 'bg' => 'bg-violet-950/20', 'text' => 'text-violet-400', 'badge' => 'bg-violet-500/20 text-violet-400 border-violet-500/40', 'icon' => 'bi-lightning-charge-fill'],
        'Fūton' => ['border' => 'border-emerald-500/70', 'bg' => 'bg-emerald-950/20', 'text' => 'text-emerald-400', 'badge' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40', 'icon' => 'bi-wind'],
        'Doton' => ['border' => 'border-yellow-700/70', 'bg' => 'bg-yellow-950/20', 'text' => 'text-yellow-600', 'badge' => 'bg-yellow-700/20 text-yellow-500 border-yellow-700/40', 'icon' => 'bi-shield-shaded'],
    ];
@endphp

<div class="max-w-4xl mx-auto py-6 sm:py-10 px-4 space-y-6">

    <!-- Bouton Retour selon le profil (Admin vers cockpit portraits, ou public) -->
    <div class="flex items-center justify-between">
        @if(auth()->check() && auth()->user()->isAdmin())
            <a href="{{ route('admin.portraits.index', ['status' => 'ready']) }}" class="inline-flex items-center gap-2 text-xs font-mono text-slate-400 hover:text-white transition">
                <i class="bi bi-arrow-left"></i>
                <span>Retour au Poste des Portraits</span>
            </a>
            <span class="badge bg-danger/20 text-danger border border-danger/40 font-mono text-xs px-3 py-1">
                VUE CONSULTATION ADMIN
            </span>
        @else
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-xs font-mono text-slate-400 hover:text-white transition">
                <i class="bi bi-arrow-left"></i>
                <span>Retour</span>
            </a>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- BLOC MAÎTRE UNIQUE : CARTE SHŌNEN JUMP & DATABOOK OFFICIEL DU NINJA      -->
    <!-- ========================================================================= -->
    <section class="rounded-3xl border border-shinobi-border/80 bg-shinobi-card p-5 sm:p-7 shadow-2xl relative overflow-hidden space-y-5">
        
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
                        Spécialisation {{ $fighter->specialization }}
                    </span>
                </div>
            </div>

            <!-- Niveau & Affinités Élémentaires -->
            <div class="flex md:flex-col items-center md:items-end justify-between gap-1.5 border-t md:border-t-0 pt-2 md:pt-0 border-shinobi-border/40 font-mono">
                <div class="text-left md:text-right">
                    <span class="text-slate-400 block text-[9px] uppercase tracking-wider">Rang & Maîtrise</span>
                    <span class="font-serif font-black text-xl text-white">Niveau {{ $fighter->level }}</span>
                </div>
                
                <div class="flex items-center gap-1.5 mt-0.5">
                    @php
                        $pStyle = $elementStyles[$fighter->primary_element] ?? ['badge' => 'bg-white/10 text-slate-300 border-white/20', 'icon' => 'bi-stars'];
                    @endphp
                    <span class="rounded {{ $pStyle['badge'] }} border px-2 py-0.5 text-[10px] font-bold flex items-center gap-1" title="Affinité Élémentaire Principale">
                        <i class="bi {{ $pStyle['icon'] }}"></i>
                        <span>{{ $fighter->primary_element }}</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Corps de la Carte : Portrait Shōnen Jump & Databook Officiel 8 Stats -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-stretch">
            
            <!-- Colonne Gauche : Portrait Shōnen Jump -->
            <div class="md:col-span-5 flex flex-col justify-between space-y-3">
                <div class="relative rounded-2xl overflow-hidden border border-shinobi-border/80 aspect-4/5 bg-black/90 shadow-2xl group">
                    @php
                        $portraitReady = $fighter->portrait_status === 'ready' && !empty($fighter->portrait_path);
                    @endphp

                    <img src="{{ $fighter->portrait_url ?? ($fighter->source_photo_url ?? asset('images/naruto-hero.png')) }}" 
                         alt="{{ $fighter->name }}" 
                         class="w-full h-full object-cover object-top {{ (!$portraitReady && !$hasPaidPass) ? 'filter blur-2xl scale-110 opacity-30 brightness-50' : 'group-hover:scale-105' }} transition-all duration-700">

                    <!-- Sceau Kanji discret en filigrane -->
                    <div class="absolute top-2 right-3 font-serif text-4xl font-black text-white/10 select-none pointer-events-none">
                        {{ $villageInfo['kanji'] }}
                    </div>

                    <div class="absolute bottom-2 left-2 right-2 flex items-center justify-between text-xs font-mono">
                        <span class="bg-shinobi-dark/90 border border-shinobi-border/80 px-2.5 py-1 rounded text-[10px] font-bold text-white shadow">
                            {{ $fighter->xp }} / {{ $xpTarget }} XP
                        </span>
                        @if($portraitReady)
                            <span class="bg-emerald-950/90 border border-emerald-500/60 text-emerald-300 font-bold px-2.5 py-1 rounded text-[10px] shadow flex items-center gap-1">
                                <i class="bi bi-patch-check-fill text-emerald-400"></i>
                                <span>Portrait Officiel HD</span>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Jauges PV & Chakra -->
                <div class="space-y-2.5 font-mono text-xs bg-white/5 rounded-2xl border border-shinobi-border/50 p-3.5">
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
                            <div class="bg-gradient-to-r from-cyan-600 to-sky-400 h-full rounded-full" style="width: {{ $chakraPercentage }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne Droite : Databook Officiel 8 Attributs -->
            <div class="md:col-span-7 flex flex-col justify-between space-y-4">
                <div class="rounded-2xl bg-white/5 border border-shinobi-border/60 p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-shinobi-border/40 pb-2">
                        <span class="font-serif font-black text-sm text-white uppercase tracking-wider">
                            Attributs Officiels du Databook
                        </span>
                        <span class="font-mono text-xs text-shinobi-gold font-bold">
                            Total : {{ number_format($fighter->getTotalDatabookScore(), 1) }} / 40.0
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 font-mono text-xs">
                        <div class="p-2 rounded-xl bg-shinobi-dark/80 border border-shinobi-border/70 flex justify-between items-center">
                            <span class="text-slate-400">Ninjutsu (NIN)</span>
                            <span class="text-white font-bold">{{ number_format($fighter->stat_nin, 1) }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-shinobi-dark/80 border border-shinobi-border/70 flex justify-between items-center">
                            <span class="text-slate-400">Taijutsu (TAI)</span>
                            <span class="text-white font-bold">{{ number_format($fighter->stat_tai, 1) }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-shinobi-dark/80 border border-shinobi-border/70 flex justify-between items-center">
                            <span class="text-slate-400">Genjutsu (GEN)</span>
                            <span class="text-white font-bold">{{ number_format($fighter->stat_gen, 1) }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-shinobi-dark/80 border border-shinobi-border/70 flex justify-between items-center">
                            <span class="text-slate-400">Intelligence (INT)</span>
                            <span class="text-white font-bold">{{ number_format($fighter->stat_int, 1) }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-shinobi-dark/80 border border-shinobi-border/70 flex justify-between items-center">
                            <span class="text-slate-400">Force (FOR)</span>
                            <span class="text-white font-bold">{{ number_format($fighter->stat_str, 1) }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-shinobi-dark/80 border border-shinobi-border/70 flex justify-between items-center">
                            <span class="text-slate-400">Vitesse (VIT)</span>
                            <span class="text-white font-bold">{{ number_format($fighter->stat_spd, 1) }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-shinobi-dark/80 border border-shinobi-border/70 flex justify-between items-center">
                            <span class="text-slate-400">Endurance (END)</span>
                            <span class="text-white font-bold">{{ number_format($fighter->stat_end, 1) }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-shinobi-dark/80 border border-shinobi-border/70 flex justify-between items-center">
                            <span class="text-slate-400">Sceaux (SCE)</span>
                            <span class="text-white font-bold">{{ number_format($fighter->stat_sea, 1) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Don Héréditaire / Talent Passif -->
                <div class="p-3.5 rounded-2xl bg-white/5 border border-shinobi-border/60 font-mono text-xs space-y-1">
                    <span class="text-emerald-400 font-bold uppercase block text-[10px] tracking-wider">Don Héréditaire de Clan</span>
                    <p class="text-slate-200 leading-relaxed">{{ $fighter->talent }}</p>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
