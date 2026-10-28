@extends('layouts.app')

@section('title', 'Tournois — Examen Chūnin')

@section('content')
<div class="space-y-6" x-data="{ 
    openTournamentModal: null, 
    filterMode: '{{ $openTournaments->isNotEmpty() ? 'open' : ($liveTournaments->isNotEmpty() ? 'live' : 'all') }}',
    nextTimestamp: {{ $nextRegistrationDate ? $nextRegistrationDate->timestamp : 0 }},
    timeLeft: { hours: '00', minutes: '00', seconds: '00' },
    initCountdown() {
        if (!this.nextTimestamp) return;
        const update = () => {
            const now = Math.floor(Date.now() / 1000);
            const diff = Math.max(0, this.nextTimestamp - now);
            const h = Math.floor(diff / 3600);
            const m = Math.floor((diff % 3600) / 60);
            const s = diff % 60;
            this.timeLeft.hours = String(h).padStart(2, '0');
            this.timeLeft.minutes = String(m).padStart(2, '0');
            this.timeLeft.seconds = String(s).padStart(2, '0');
        };
        update();
        setInterval(update, 1000);
    }
}" x-init="initCountdown()">

    @php
        $userFighter = auth()->check() ? auth()->user()->fighter : null;
        $allCards = $openTournaments->concat($liveTournaments)->concat($completedTournaments)->unique('id');
    @endphp

    <!-- ========================================================================= -->
    <!-- 1. HERO BANNER : TITRE TOURNOIS + PARCHEMIN CITATION                       -->
    <!-- ========================================================================= -->
    <div class="relative overflow-hidden rounded-3xl border border-shinobi-border bg-gradient-to-r from-shinobi-dark via-[#1A1218] to-shinobi-card p-6 sm:p-8 shadow-2xl">
        <div class="pointer-events-none absolute -right-16 -top-16 h-80 w-80 rounded-full bg-shinobi-red/15 blur-3xl"></div>
        <div class="pointer-events-none absolute left-0 top-0 h-full w-48 bg-gradient-to-r from-shinobi-red/10 to-transparent"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            
            <!-- Titre & Lore gauche -->
            <div class="flex items-center gap-5">
                <div class="relative hidden sm:block w-20 h-20 rounded-2xl overflow-hidden border border-shinobi-red/50 shadow-lg shadow-shinobi-red/20 shrink-0">
                    <img src="{{ asset('images/naruto-hero.png') }}" alt="Tournois Shinobi" class="w-full h-full object-cover object-top">
                </div>
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 rounded-full border border-shinobi-red/30 bg-shinobi-red/10 px-3 py-0.5 font-mono text-[10px] font-bold tracking-wider text-shinobi-red uppercase">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-shinobi-red animate-pulse"></span>
                        LIGUE OFFICIELLE D'ÉLIMINATION DIRECTE
                    </div>
                    <h1 class="font-serif text-3xl sm:text-4xl font-black uppercase tracking-tight text-white">
                        TOURNOIS
                    </h1>
                    <p class="font-mono text-xs text-slate-300 max-w-xl">
                        Affronte les meilleurs shinobis et prouve ta valeur dans l'Examen Chūnin.
                    </p>
                </div>
            </div>

            <!-- Parchemin Citation Droite -->
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <a href="{{ route('tournaments.private.index') }}" 
                   class="w-full sm:w-auto px-5 py-3 rounded-2xl bg-gradient-to-r from-shinobi-red via-red-600 to-shinobi-gold text-white font-serif font-black uppercase text-xs tracking-wider shadow-lg shadow-shinobi-red/30 hover:scale-[1.02] active:scale-[0.98] transition flex items-center justify-center gap-2.5 text-decoration-none">
                    <i class="bi bi-people-fill text-base"></i>
                    <span>Salons Privés Entre Amis</span>
                    <span class="rounded-full bg-black/40 px-2 py-0.5 font-mono text-[10px] text-amber-300">Nouveau</span>
                </a>

                <div class="rounded-2xl border border-[#DFC4B5] bg-[#F5E0D7] p-4 text-[#181310] shadow-xl max-w-xs relative overflow-hidden shrink-0 hidden md:block">
                    <div class="flex items-start justify-between gap-3">
                        <p class="font-serif italic text-xs text-[#3E2B20] leading-relaxed">
                            « Ce n'est pas la force qui fait un shinobi, mais sa détermination à se relever. »
                        </p>
                        <div class="w-7 h-7 rounded border-2 border-shinobi-red flex items-center justify-center font-serif text-[9px] font-black text-shinobi-red select-none shrink-0">
                            忍道
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. BARRE DE FILTRAGE PAR MENU DÉROULANT (DROPDOWN PUR SANS EMOJI)         -->
    <!-- ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-shinobi-border/60 pb-4 font-mono text-xs">
        
        <!-- Titre dynamique du filtre actif -->
        <div class="flex items-center gap-2">
            <span class="font-mono text-[10px] font-bold uppercase tracking-wider text-shinobi-red">FILTRER LES SESSIONS :</span>
            <span class="font-serif text-sm sm:text-base font-bold text-white" 
                  x-text="filterMode === 'open' ? 'Inscriptions Ouvertes' : (filterMode === 'live' ? 'En Direct & Arbres Figés' : (filterMode === 'my' ? 'Mes Inscriptions' : 'Toutes les Sessions'))">
            </span>
        </div>

        <!-- Menu Déroulant (Dropdown Sobre) -->
        <div class="w-full sm:w-auto">
            <select x-model="filterMode" 
                    class="w-full sm:w-auto rounded-xl border border-shinobi-border bg-shinobi-card hover:border-shinobi-red/60 text-white font-mono text-xs px-4 py-2.5 focus:border-shinobi-red focus:outline-none transition cursor-pointer shadow-lg">
                <option value="open">
                    Inscriptions ouvertes ({{ $openTournaments->count() }})
                </option>
                <option value="live">
                    En direct & Arbres verrouillés ({{ $liveTournaments->count() }})
                </option>
                <option value="my">
                    Mes inscriptions ({{ $myTournaments->count() }})
                </option>
                <option value="all">
                    Toutes les sessions ({{ $allCards->count() }})
                </option>
            </select>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 3. COMPTE À REBOURS ÉLÉGANT SI AUCUN TOURNOI OUVERT                       -->
    <!-- ========================================================================= -->
    <div x-show="filterMode === 'open' && {{ $openTournaments->isEmpty() ? 'true' : 'false' }}"
         class="glass3d rounded-2xl border border-shinobi-border/70 p-8 sm:p-10 text-center space-y-6">
        
        <div class="w-16 h-16 mx-auto rounded-2xl bg-white/5 border border-shinobi-border flex items-center justify-center text-shinobi-red text-2xl shadow-lg">
            <i class="bi bi-hourglass-split"></i>
        </div>

        <div class="space-y-2 max-w-lg mx-auto">
            <h3 class="font-serif text-xl sm:text-2xl font-black uppercase text-white tracking-wide">
                Aucune Inscription Ouverte Actuellement
            </h3>
            <p class="font-mono text-xs text-slate-300 leading-relaxed">
                Les tournois officiels de l'Examen Chūnin ont lieu tous les <strong class="text-shinobi-gold">Lundi, Mercredi et Vendredi</strong>. Les inscriptions ouvrent automatiquement à <strong class="text-white">08h00</strong>.
            </p>
        </div>

        @if($nextRegistrationDate)
            <!-- Chrono Digital -->
            <div class="pt-2">
                <span class="font-mono text-[10px] uppercase font-bold tracking-wider text-slate-400 block mb-3">
                    Prochaine ouverture des inscriptions : {{ $nextRegistrationDate->translatedFormat('l d F à H:i') }}
                </span>
                
                <div class="inline-flex items-center gap-3 bg-black/40 border border-shinobi-border rounded-2xl px-6 py-4 font-mono shadow-inner">
                    <div class="text-center">
                        <span class="block text-2xl sm:text-3xl font-bold text-white font-mono" x-text="timeLeft.hours">00</span>
                        <span class="text-[9px] uppercase tracking-wider text-slate-500">Heures</span>
                    </div>
                    <span class="text-2xl text-slate-600 font-bold -mt-3">:</span>
                    <div class="text-center">
                        <span class="block text-2xl sm:text-3xl font-bold text-white font-mono" x-text="timeLeft.minutes">00</span>
                        <span class="text-[9px] uppercase tracking-wider text-slate-500">Minutes</span>
                    </div>
                    <span class="text-2xl text-slate-600 font-bold -mt-3">:</span>
                    <div class="text-center">
                        <span class="block text-2xl sm:text-3xl font-bold text-shinobi-gold font-mono" x-text="timeLeft.seconds">00</span>
                        <span class="text-[9px] uppercase tracking-wider text-slate-500">Secondes</span>
                    </div>
                </div>
            </div>
        @endif

    </div>

    <!-- ========================================================================= -->
    <!-- 4. GRILLE DES CARTES DE TOURNOIS DISPONIBLES                               -->
    <!-- ========================================================================= -->
    <div class="space-y-4">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($allCards as $t)
                @php
                    $isUserIn = $userFighter && $t->entries->contains('fighter_id', $userFighter->id);
                    $isOpen = $t->status === 'registration';
                    $isLive = in_array($t->status, ['locked', 'running']);
                @endphp
                
                <div class="rounded-2xl border border-shinobi-border bg-shinobi-card/80 hover:bg-shinobi-card hover:border-shinobi-border/90 transition-all duration-300 p-5 flex flex-col justify-between shadow-lg"
                     x-show="filterMode === 'all' || (filterMode === 'open' && '{{ $isOpen ? '1' : '0' }}' === '1') || (filterMode === 'live' && '{{ $isLive ? '1' : '0' }}' === '1') || (filterMode === 'my' && '{{ $isUserIn ? '1' : '0' }}' === '1')">
                    
                    <div>
                        <!-- Header Carte -->
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2">
                                @if($t->status === 'registration')
                                    <span class="rounded-md bg-emerald-500/10 border border-emerald-500/30 px-2 py-0.5 font-mono text-[10px] font-bold text-emerald-400 uppercase">
                                        Inscriptions ouvertes
                                    </span>
                                @elseif($t->status === 'locked')
                                    <span class="rounded-md bg-shinobi-gold/15 border border-shinobi-gold/40 px-2 py-0.5 font-mono text-[10px] font-bold text-shinobi-gold uppercase">
                                        Arbre verrouillé
                                    </span>
                                @elseif($t->status === 'running')
                                    <span class="rounded-md bg-shinobi-red/10 border border-shinobi-red/30 px-2 py-0.5 font-mono text-[10px] font-bold text-shinobi-red uppercase">
                                        Combats en cours
                                    </span>
                                @elseif($t->status === 'completed')
                                    <span class="rounded-md bg-white/10 border border-white/20 px-2 py-0.5 font-mono text-[10px] font-bold text-slate-300 uppercase">
                                        Terminé
                                    </span>
                                @else
                                    <span class="rounded-md bg-white/5 border border-shinobi-border px-2 py-0.5 font-mono text-[10px] font-bold text-slate-400 uppercase">
                                        {{ $t->status_label }}
                                    </span>
                                @endif

                                @if($isUserIn)
                                    <span class="rounded-md bg-shinobi-gold/10 border border-shinobi-gold/30 px-2 py-0.5 font-mono text-[10px] font-bold text-shinobi-gold uppercase">
                                        Inscrit
                                    </span>
                                @endif
                            </div>

                            <span class="font-mono text-xs font-bold text-slate-300">
                                <strong class="text-white">{{ $t->entries_count }}</strong>/8 shinobis
                            </span>
                        </div>

                        <!-- Titre du Tournoi -->
                        <h3 class="font-serif text-lg font-bold text-white tracking-wide">
                            {{ $t->title }}
                        </h3>

                        <!-- Horaires et Récompense Dynamiques -->
                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs font-mono text-slate-400 pt-3 border-t border-shinobi-border/60">
                            <div>
                                <span class="text-[10px] uppercase text-slate-500 block">
                                    @if($t->status === 'registration')
                                        Fin Inscriptions
                                    @elseif($t->status === 'locked' || $t->status === 'running')
                                        Début des Duels
                                    @else
                                        Clôturé le
                                    @endif
                                </span>
                                <span class="text-slate-200 font-semibold">
                                    @if($t->status === 'registration' && $t->registration_closes_at)
                                        {{ $t->registration_closes_at->translatedFormat('H:i') }}
                                        <span class="text-[10px] text-emerald-400 font-normal">({{ $t->registration_closes_at->diffForHumans(['parts' => 1]) }})</span>
                                    @elseif($t->starts_at)
                                        {{ $t->starts_at->translatedFormat('D d M à H:i') }}
                                    @else
                                        Dès 8 inscrits
                                    @endif
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] uppercase text-slate-500 block">Récompense</span>
                                <span class="text-shinobi-gold font-bold">+300 XP</span>
                                <span class="text-[10px] text-slate-400 block">+100 XP / tour gagné</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Carte -->
                    <div class="mt-5 pt-3 border-t border-shinobi-border/40 flex items-center gap-2">
                        <!-- BOUTON QUI OUVRE LA MODALE DU BRACKET -->
                        <button type="button" 
                                @click="openTournamentModal = {{ $t->id }}"
                                class="flex-1 text-center rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-200 hover:text-white px-3 py-2 font-mono text-xs font-semibold transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="bi bi-diagram-3 text-shinobi-red"></i>
                            <span>Voir l'arbre</span>
                        </button>

                        <!-- Bouton Inscription / Accéder / État verrouillé -->
                        @if($t->status === 'registration')
                            @if($isUserIn)
                                <form method="POST" action="{{ route('tournaments.unregister', $t) }}">
                                    @csrf
                                    <button type="submit" class="rounded-xl border border-red-500/50 bg-red-950/40 hover:bg-red-900/60 text-red-300 px-4 py-2 font-mono text-xs font-bold transition">
                                        Quitter
                                    </button>
                                </form>
                            @elseif($t->entries_count < 8 && $userFighter)
                                <form method="POST" action="{{ route('tournaments.register', $t) }}">
                                    @csrf
                                    <button type="submit" class="rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white px-4 py-2 font-mono text-xs font-bold shadow-lg shadow-shinobi-red/20 transition">
                                        S'inscrire
                                    </button>
                                </form>
                            @endif
                        @elseif($t->status === 'locked')
                            <span class="rounded-xl border border-shinobi-border bg-white/5 text-slate-400 px-3 py-2 font-mono text-xs select-none">
                                Inscriptions closes
                            </span>
                        @elseif($t->status === 'running')
                            <a href="{{ route('tournaments.show', $t) }}" class="rounded-xl bg-gradient-to-r from-shinobi-red to-[#B0060F] text-white px-4 py-2 font-mono text-xs font-bold transition shadow">
                                Combat en direct
                            </a>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 5. MODALE POPUP IMMERSIVE DU BRACKET                                      -->
    <!-- ========================================================================= -->
    @foreach($allCards as $t)
        <div x-show="openTournamentModal === {{ $t->id }}" 
             x-cloak 
             @keydown.escape.window="openTournamentModal = null"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-6"
             style="display: none;">
            
            <div class="fixed inset-0" @click="openTournamentModal = null"></div>

            <div class="relative w-full max-w-5xl rounded-3xl border border-shinobi-border/80 bg-shinobi-card p-5 sm:p-7 shadow-2xl space-y-6 z-10 overflow-hidden"
                 @click.stop>
                
                <!-- En-tête Modale -->
                <div class="flex items-start justify-between gap-4 border-b border-shinobi-border/60 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[10px] font-bold uppercase tracking-wider text-shinobi-red">ARBRE ÉLIMINATOIRE OFFICIEL</span>
                            <span class="rounded bg-white/5 border border-shinobi-border px-2 py-0.5 font-mono text-[10px] text-slate-300">
                                8 Participants • 3 Tours
                            </span>
                            <span class="rounded bg-shinobi-red/10 border border-shinobi-red/30 px-2 py-0.5 font-mono text-[10px] font-bold text-shinobi-red uppercase">
                                {{ $t->status_label }}
                            </span>
                        </div>
                        <h2 class="font-serif text-2xl sm:text-3xl font-black uppercase text-white mt-1">
                            {{ $t->title }}
                        </h2>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('tournaments.show', $t) }}" class="hidden sm:inline-flex items-center gap-1.5 rounded-xl border border-shinobi-gold/40 bg-shinobi-gold/10 hover:bg-shinobi-gold/20 text-shinobi-gold px-3 py-1.5 font-mono text-xs font-semibold transition">
                            <i class="bi bi-box-arrow-up-right"></i> Plein écran
                        </a>
                        <button type="button" 
                                @click="openTournamentModal = null"
                                class="w-9 h-9 rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white flex items-center justify-center transition cursor-pointer"
                                title="Fermer (Échap)">
                            <i class="bi bi-x-lg text-sm"></i>
                        </button>
                    </div>
                </div>

                @if($t->matches->isEmpty())
                    <div class="py-12 text-center space-y-3 font-mono text-xs text-slate-400">
                        <div class="w-16 h-16 mx-auto rounded-2xl bg-white/5 border border-shinobi-border flex items-center justify-center text-slate-400 text-2xl">
                            <i class="bi bi-diagram-3"></i>
                        </div>
                        <p class="text-sm text-slate-200 font-semibold">Le tableau officiel sera généré à la validation des 8 inscriptions.</p>
                        <p class="text-slate-400 max-w-md mx-auto">
                            Actuellement <strong class="text-white">{{ $t->entries_count }}/8</strong> combattants inscrits. Le tirage au sort figera automatiquement les 4 quarts de finale.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto pb-4">
                        <div class="min-w-[680px] grid grid-cols-4 gap-4 items-center relative py-4">
                            
                            @php
                                $matches = $t->matches;
                                $round1Matches = $matches->where('round', 1)->values();
                                $round2Matches = $matches->where('round', 2)->values();
                                $round3Match = $matches->where('round', 3)->first();
                                $winner = $round3Match?->winner;
                            @endphp

                            <!-- COLONNE 1 : 1/4 DE FINALE (4 Matchs) -->
                            <div class="space-y-4">
                                <div class="text-center font-mono text-[10px] uppercase font-bold text-slate-400 mb-2 border-b border-shinobi-border/40 pb-1">
                                    1/4 de Finale
                                </div>
                                @for($i = 0; $i < 4; $i++)
                                    @php $m = $round1Matches->get($i); @endphp
                                    <div class="rounded-xl border {{ $m && $m->winner_id ? 'border-shinobi-gold/60 bg-shinobi-dark' : 'border-shinobi-border bg-white/5' }} p-2.5 space-y-1.5 font-mono text-xs shadow-sm">
                                        <div class="flex items-center justify-between gap-1.5 p-1 rounded {{ $m && $m->winner_id === $m->fighter_a_id ? 'bg-shinobi-gold/15 text-shinobi-gold font-bold' : 'text-slate-300' }}">
                                            <span class="truncate text-[11px]">{{ $m?->fighterA?->name ?? 'Participant ' . (($i * 2) + 1) }}</span>
                                            @if($m && $m->winner_id === $m->fighter_a_id)
                                                <i class="bi bi-trophy-fill text-[10px] text-shinobi-gold shrink-0"></i>
                                            @endif
                                        </div>
                                        <div class="flex items-center justify-between gap-1.5 p-1 rounded {{ $m && $m->winner_id === $m->fighter_b_id ? 'bg-shinobi-gold/15 text-shinobi-gold font-bold' : 'text-slate-300' }}">
                                            <span class="truncate text-[11px]">{{ $m?->fighterB?->name ?? 'Participant ' . (($i * 2) + 2) }}</span>
                                            @if($m && $m->winner_id === $m->fighter_b_id)
                                                <i class="bi bi-trophy-fill text-[10px] text-shinobi-gold shrink-0"></i>
                                            @endif
                                        </div>
                                    </div>
                                @endfor
                            </div>

                            <!-- COLONNE 2 : 1/2 FINALE (2 Matchs) -->
                            <div class="space-y-14 my-auto">
                                <div class="text-center font-mono text-[10px] uppercase font-bold text-slate-400 mb-2 border-b border-shinobi-border/40 pb-1">
                                    1/2 Finale
                                </div>
                                @for($i = 0; $i < 2; $i++)
                                    @php $m = $round2Matches->get($i); @endphp
                                    <div class="rounded-xl border {{ $m && $m->winner_id ? 'border-shinobi-gold/60 bg-shinobi-dark' : 'border-shinobi-border bg-white/5' }} p-2.5 space-y-1.5 font-mono text-xs shadow-sm">
                                        <div class="flex items-center justify-between gap-1.5 p-1 rounded {{ $m && $m->winner_id === $m->fighter_a_id ? 'bg-shinobi-gold/15 text-shinobi-gold font-bold' : 'text-slate-300' }}">
                                            <span class="truncate text-[11px]">{{ $m?->fighterA?->name ?? 'Vainqueur 1/4' }}</span>
                                            @if($m && $m->winner_id === $m->fighter_a_id)
                                                <i class="bi bi-trophy-fill text-[10px] text-shinobi-gold shrink-0"></i>
                                            @endif
                                        </div>
                                        <div class="flex items-center justify-between gap-1.5 p-1 rounded {{ $m && $m->winner_id === $m->fighter_b_id ? 'bg-shinobi-gold/15 text-shinobi-gold font-bold' : 'text-slate-300' }}">
                                            <span class="truncate text-[11px]">{{ $m?->fighterB?->name ?? 'Vainqueur 1/4' }}</span>
                                            @if($m && $m->winner_id === $m->fighter_b_id)
                                                <i class="bi bi-trophy-fill text-[10px] text-shinobi-gold shrink-0"></i>
                                            @endif
                                        </div>
                                    </div>
                                @endfor
                            </div>

                            <!-- COLONNE 3 : FINALE (1 Match) -->
                            <div class="my-auto space-y-2">
                                <div class="text-center font-mono text-[10px] uppercase font-bold text-slate-400 mb-2 border-b border-shinobi-border/40 pb-1">
                                    Finale
                                </div>
                                <div class="rounded-xl border {{ $round3Match && $round3Match->winner_id ? 'border-shinobi-gold/60 bg-shinobi-dark' : 'border-shinobi-border bg-white/5' }} p-2.5 space-y-1.5 font-mono text-xs shadow-sm">
                                    <div class="flex items-center justify-between gap-1.5 p-1 rounded {{ $round3Match && $round3Match->winner_id === $round3Match->fighter_a_id ? 'bg-shinobi-gold/15 text-shinobi-gold font-bold' : 'text-slate-300' }}">
                                        <span class="truncate text-[11px]">{{ $round3Match?->fighterA?->name ?? 'Finaliste 1' }}</span>
                                        @if($round3Match && $round3Match->winner_id === $round3Match->fighter_a_id)
                                            <i class="bi bi-trophy-fill text-[10px] text-shinobi-gold shrink-0"></i>
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between gap-1.5 p-1 rounded {{ $round3Match && $round3Match->winner_id === $round3Match->fighter_b_id ? 'bg-shinobi-gold/15 text-shinobi-gold font-bold' : 'text-slate-300' }}">
                                        <span class="truncate text-[11px]">{{ $round3Match?->fighterB?->name ?? 'Finaliste 2' }}</span>
                                        @if($round3Match && $round3Match->winner_id === $round3Match->fighter_b_id)
                                            <i class="bi bi-trophy-fill text-[10px] text-shinobi-gold shrink-0"></i>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- COLONNE 4 : VAINQUEUR / PODIUM CHŪNIN -->
                            <div class="my-auto text-center space-y-3">
                                <div class="relative inline-block">
                                    <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-shinobi-gold/20 via-shinobi-gold/10 to-transparent border-2 border-shinobi-gold flex items-center justify-center text-shinobi-gold text-2xl shadow-xl shadow-shinobi-gold/20">
                                        <i class="bi bi-trophy-fill"></i>
                                    </div>
                                    <span class="block font-serif font-black text-xs uppercase tracking-wider text-shinobi-gold mt-2">
                                        Vainqueur
                                    </span>
                                </div>

                                <div class="rounded-xl border border-shinobi-gold/40 bg-shinobi-card p-3 shadow-lg">
                                    <h4 class="font-serif font-bold text-sm text-white truncate">
                                        {{ $winner?->name ?? 'En attente...' }}
                                    </h4>
                                    <span class="font-mono text-[10px] text-slate-400 block mt-0.5">
                                        {{ $winner ? 'Clan ' . $winner->clan : 'Titre Chūnin' }}
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>
                @endif

                <div class="pt-4 border-t border-shinobi-border/60 flex items-center justify-between text-xs font-mono text-slate-400">
                    <span class="italic font-serif text-slate-300">« Le véritable shinobi ne cherche pas la victoire, il cherche à se surpasser. »</span>
                    <button type="button" 
                            @click="openTournamentModal = null"
                            class="rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 px-4 py-2 text-slate-300 hover:text-white transition cursor-pointer">
                        Fermer l'arbre
                    </button>
                </div>

            </div>
        </div>
    @endforeach

</div>
@endsection
