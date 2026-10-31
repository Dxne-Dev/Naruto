@extends('layouts.app')

@section('title', 'Combats — Arène & Replays')

@section('content')
<div class="space-y-6 pb-16" x-data="{ activeTab: 'my_combats' }">

    <!-- ========================================================================= -->
    <!-- NAVIGATION PAR ONGLETS DYNAMIQUES (Mes Combats vs Arène Générale)          -->
    <!-- ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-shinobi-border pb-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-serif font-black tracking-tight text-white uppercase flex items-center gap-2">
                <span>Arène des Combats</span>
            </h1>
            <p class="text-xs sm:text-sm font-mono text-slate-400 mt-1">
                Suis tes affrontements de tournoi, prépare tes duels et analyse les replays.
            </p>
        </div>

        <!-- Boutons d'onglets immersifs -->
        <div class="flex items-center gap-2 p-1.5 rounded-2xl bg-shinobi-card/90 border border-shinobi-border shrink-0 font-mono text-xs">
            <button type="button" 
                    @click="activeTab = 'my_combats'" 
                    class="px-4 py-2 rounded-xl font-bold transition-all flex items-center gap-2"
                    :class="activeTab === 'my_combats' ? 'bg-shinobi-red text-white shadow-lg shadow-shinobi-red/30' : 'text-slate-400 hover:text-white hover:bg-white/5'">
                <i class="bi bi-person-fill"></i>
                <span>Mes Combats</span>
                @if($myUpcomingMatch)
                    <span class="w-2 h-2 rounded-full bg-shinobi-gold animate-ping"></span>
                @endif
            </button>

            <button type="button" 
                    @click="activeTab = 'community'" 
                    class="px-4 py-2 rounded-xl font-bold transition-all flex items-center gap-2"
                    :class="activeTab === 'community' ? 'bg-shinobi-red text-white shadow-lg shadow-shinobi-red/30' : 'text-slate-400 hover:text-white hover:bg-white/5'">
                <i class="bi bi-globe2"></i>
                <span>Arène Générale</span>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- ONGLET 1 : MES COMBATS & MES TOURNOIS (CENTRE SUR LE JOUEUR)              -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'my_combats'" class="space-y-6">

        @if(!$fighter)
            <!-- Pas encore de combattant -->
            <div class="rounded-3xl border border-shinobi-border bg-gradient-to-b from-shinobi-card to-shinobi-dark p-8 sm:p-12 text-center shadow-xl space-y-4">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-shinobi-red/10 border border-shinobi-red/30 flex items-center justify-center text-shinobi-red text-2xl">
                    <i class="bi bi-person-x-fill text-2xl"></i>
                </div>
                <h3 class="font-serif text-2xl font-black uppercase text-white">Aucun Shinobi Forgé</h3>
                <p class="font-mono text-xs text-slate-300 max-w-md mx-auto">
                    Tu dois d'abord créer et forger ton personnage ninja pour participer aux combats et tournois de l'arène.
                </p>
                <div class="pt-2">
                    <a href="{{ route('onboarding.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white font-mono text-xs font-bold shadow-lg shadow-shinobi-red/30 transition text-decoration-none">
                        <span>Créer mon Shinobi</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        @else

            <!-- 1. CARTE SITUATION TOURNOI ACTIF / PROCHAIN COMBAT -->
            @if($myUpcomingMatch)
                <!-- CAS A : Un combat précis est déjà planifié avec un rival -->
                @php
                    $isFighterA = $myUpcomingMatch->fighter_a_id === $fighter->id;
                    $mySide = $isFighterA ? $myUpcomingMatch->fighterA : $myUpcomingMatch->fighterB;
                    $opponent = $isFighterA ? $myUpcomingMatch->fighterB : $myUpcomingMatch->fighterA;
                @endphp
                <div class="relative rounded-3xl border-2 border-shinobi-gold/60 bg-gradient-to-b from-[#201815] via-shinobi-card to-[#120E14] p-6 sm:p-8 shadow-[0_15px_40px_rgba(244,202,48,0.15)] overflow-hidden">
                    <div class="pointer-events-none absolute -right-20 -top-20 w-64 h-64 rounded-full bg-shinobi-gold/15 blur-3xl"></div>

                    <!-- En-tête -->
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-6 border-b border-shinobi-gold/30 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-shinobi-gold animate-ping"></span>
                            <span class="font-mono text-xs font-bold uppercase tracking-wider text-shinobi-gold">
                                PROCHAIN DUEL PROGRAMMÉ • {{ $myUpcomingMatch->tournament?->title ?? 'Examen Chūnin' }}
                            </span>
                        </div>
                        <span class="font-mono text-[11px] text-slate-400">
                            {{ $myUpcomingMatch->scheduled_at ? 'Prévu le ' . $myUpcomingMatch->scheduled_at->format('d/m/Y à H:i') : 'Combat imminent' }}
                        </span>
                    </div>

                    <!-- Face-à-face VS -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                        <!-- Moi -->
                        <div class="sm:col-span-5 p-4 rounded-2xl bg-shinobi-dark/80 border border-shinobi-border flex items-center gap-3">
                            <div class="w-14 h-14 rounded-xl overflow-hidden border border-shinobi-gold/50 shrink-0 bg-black">
                                <img src="{{ $mySide?->display_avatar_url ?? asset('images/naruto-hero.png') }}" alt="{{ $mySide?->name }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='{{ asset('images/naruto-hero.png') }}';">
                            </div>
                            <div class="min-w-0">
                                <span class="badge bg-shinobi-gold/20 text-shinobi-gold border border-shinobi-gold/40 font-mono text-[9px] mb-1">MON SHINOBI</span>
                                <h4 class="font-serif font-black text-white text-base truncate">{{ $mySide?->name }}</h4>
                                <span class="font-mono text-[10px] text-slate-400 block">Niveau {{ $mySide?->level }} • Clan {{ $mySide?->clan }}</span>
                            </div>
                        </div>

                        <!-- VS Badge -->
                        <div class="sm:col-span-2 text-center py-2">
                            <div class="w-10 h-10 mx-auto rounded-full bg-shinobi-red/20 border border-shinobi-red text-shinobi-red flex items-center justify-center font-serif font-black text-sm shadow-lg shadow-shinobi-red/30">
                                VS
                            </div>
                        </div>

                        <!-- Adversaire -->
                        <div class="sm:col-span-5 p-4 rounded-2xl bg-shinobi-dark/80 border border-shinobi-border flex items-center justify-between sm:justify-start gap-3 text-right sm:text-left">
                            <div class="w-14 h-14 rounded-xl overflow-hidden border border-shinobi-border shrink-0 bg-black order-last sm:order-first">
                                <img src="{{ $opponent?->display_avatar_url ?? asset('images/naruto-hero.png') }}" alt="{{ $opponent?->name }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='{{ asset('images/naruto-hero.png') }}';">
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="badge bg-white/10 text-slate-300 font-mono text-[9px] mb-1">ADVERSAIRE</span>
                                <h4 class="font-serif font-black text-white text-base truncate">{{ $opponent?->name ?? 'En attente' }}</h4>
                                <span class="font-mono text-[10px] text-slate-400 block">Niveau {{ $opponent?->level ?? 1 }} • Clan {{ $opponent?->clan ?? 'Shinobi' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Call to action duel -->
                    <div class="mt-6 pt-4 border-t border-shinobi-border/60 flex flex-wrap items-center justify-between gap-3">
                        <span class="text-xs font-mono text-slate-300">
                            💡 Reste connecté ou ajuste ta stratégie tactique avant le tirage au sort de l'arbitre.
                        </span>
                        <a href="{{ route('strategies.index') }}" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-mono text-xs font-bold transition flex items-center gap-1.5 text-decoration-none">
                            <i class="bi bi-sliders"></i>
                            <span>Ajuster ma Tactique</span>
                        </a>
                    </div>
                </div>

            @elseif($myActiveTournamentEntry)
                <!-- CAS B : Inscrit à un tournoi, mais arbre pas encore forgé ou en attente d'adversaire -->
                @php
                    $activeTourn = $myActiveTournamentEntry->tournament;
                @endphp
                <div class="rounded-3xl border border-shinobi-chakra/40 bg-gradient-to-b from-[#131924] via-shinobi-card to-shinobi-dark p-6 sm:p-8 shadow-xl">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-shinobi-chakra/10 border border-shinobi-chakra/40 text-shinobi-chakra flex items-center justify-center text-xl shrink-0">
                                <i class="bi bi-patch-check-fill"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="badge bg-shinobi-chakra/20 text-shinobi-chakra border border-shinobi-chakra/40 font-mono text-[10px]">
                                        INSCRIPTION CONFIRMÉE
                                    </span>
                                    <span class="font-mono text-xs text-slate-400">#{{ $activeTourn->id }}</span>
                                </div>
                                <h3 class="font-serif font-black text-white text-lg sm:text-xl uppercase">
                                    {{ $activeTourn->title }}
                                </h3>
                                <p class="font-mono text-xs text-slate-300 mt-1">
                                    Ton shinobi <strong class="text-white">{{ $fighter->name }}</strong> est officiellement inscrit sur le tableau. L'arbitrage débutera dès la clôture des inscriptions.
                                </p>
                            </div>
                        </div>

                        <div class="shrink-0 flex items-center gap-2">
                            <a href="{{ route('tournaments.show', $activeTourn) }}" class="px-5 py-2.5 rounded-xl bg-shinobi-chakra/20 hover:bg-shinobi-chakra/30 border border-shinobi-chakra/50 text-shinobi-chakra font-mono text-xs font-bold transition text-decoration-none flex items-center gap-1.5">
                                <i class="bi bi-diagram-3-fill"></i>
                                <span>Voir l'Arbre du Tournoi</span>
                            </a>
                        </div>
                    </div>
                </div>

            @else
                <!-- CAS C : Le joueur n'est inscrit à aucun tournoi actif -->
                <div class="rounded-3xl border border-shinobi-border bg-gradient-to-b from-[#1A151C] via-shinobi-card to-shinobi-dark p-6 sm:p-8 shadow-xl">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <div class="space-y-2">
                            <span class="badge bg-shinobi-red/20 text-rose-300 border border-shinobi-red/40 font-mono text-[10px]">
                                AUCUN COMBAT OFFICIEL EN ATTENTE
                            </span>
                            <h3 class="font-serif font-black text-xl sm:text-2xl text-white uppercase">
                                Prépare ton shinobi pour l'Examen Chūnin
                            </h3>
                            <p class="font-mono text-xs sm:text-sm text-slate-300 max-w-xl leading-relaxed">
                                @if($openTournament)
                                    Le tournoi <strong class="text-white">{{ $openTournament->title }}</strong> est actuellement ouvert aux inscriptions ({{ $openTournament->entries_count }}/16 shinobi inscrits).
                                @else
                                    Aucun examen Chūnin n'est ouvert actuellement, mais tu peux lancer un duel d'entraînement immédiat pour gagner de l'XP.
                                @endif
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                            @if($openTournament)
                                <a href="{{ route('tournaments.show', $openTournament) }}" class="px-5 py-3 rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white font-mono text-xs font-bold shadow-lg shadow-shinobi-red/30 transition text-decoration-none flex items-center justify-center gap-2">
                                    <i class="bi bi-trophy-fill"></i>
                                    <span>Rejoindre le Tournoi ▸</span>
                                </a>
                            @endif

                            <form method="POST" action="{{ route('battles.training') }}">
                                @csrf
                                <button type="submit" class="w-full px-5 py-3 rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 text-white font-mono text-xs font-bold transition flex items-center justify-center gap-2">
                                    <i class="bi bi-lightning-charge-fill text-shinobi-gold"></i>
                                    <span>Duel d'Entraînement</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 2. HISTORIQUE PERSONNEL DU JOUEUR (MES DERNIERS MATCHS) -->
            <div class="rounded-2xl border border-shinobi-border bg-shinobi-card/80 p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-clock-history text-shinobi-red"></i>
                        <h3 class="font-serif font-black uppercase text-base text-white tracking-wide">
                            Historique Personnel de {{ $fighter->name }}
                        </h3>
                    </div>
                    @if($myBattles->isNotEmpty())
                        <span class="font-mono text-[10px] text-slate-400">{{ $myBattles->total() }} COMBAT(S) DISPUTÉ(S)</span>
                    @endif
                </div>

                @if($myBattles->isEmpty())
                    <div class="py-8 text-center font-mono text-xs text-slate-400 space-y-2">
                        <i class="bi bi-shield-slash text-2xl text-slate-500 block"></i>
                        <p>Tu n'as pas encore disputé de combat officiel avec ce shinobi.</p>
                        <form method="POST" action="{{ route('battles.training') }}" class="pt-2">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-mono text-xs font-bold transition">
                                Lancer un premier duel d'essai ▸
                            </button>
                        </form>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 font-mono text-xs">
                        @foreach($myBattles as $b)
                            @php
                                $isVictory = $b->winner_id === $fighter->id;
                                $opponent = ($b->fighter_a_id === $fighter->id) ? $b->fighterB : $b->fighterA;
                            @endphp
                            <div class="p-3.5 rounded-xl border {{ $isVictory ? 'border-emerald-500/40 bg-emerald-950/15' : 'border-rose-500/40 bg-rose-950/15' }} flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded font-bold text-[9px] uppercase {{ $isVictory ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
                                            {{ $isVictory ? 'VICTOIRE' : 'DÉFAITE' }}
                                        </span>
                                        <span class="text-[10px] text-slate-400">{{ $b->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="text-white font-bold truncate">
                                        vs {{ $opponent?->name ?? 'Rival' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        Clan {{ $opponent?->clan ?? 'Shinobi' }} • {{ $b->match?->tournament?->title ?? 'Duel d\'arène' }}
                                    </div>
                                </div>

                                <a href="{{ route('battles.show', $b) }}" class="p-2.5 rounded-xl bg-white/5 hover:bg-white/15 text-slate-300 hover:text-white transition shrink-0" title="Revoir le duel">
                                    <i class="bi bi-play-circle-fill text-lg"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    @if($myBattles->hasPages())
                        <div class="pt-3 border-t border-shinobi-border/60">
                            {{ $myBattles->links() }}
                        </div>
                    @endif
                @endif
            </div>

        @endif

    </div>

    <!-- ========================================================================= -->
    <!-- ONGLET 2 : ARÈNE GÉNÉRALE (TOUS LES AUTRES COMBATS ET TOURNOIS)           -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'community'" x-cloak class="space-y-6">

        <!-- Prochains Duels programmés dans les tournois -->
        @if($otherUpcomingMatches->isNotEmpty())
            <div class="rounded-2xl border border-shinobi-border bg-shinobi-card/80 p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-2">
                    <span class="font-mono text-xs uppercase font-bold text-slate-300 flex items-center gap-2">
                        <i class="bi bi-calendar-event text-shinobi-gold"></i>
                        <span>Prochains Duels au Programme</span>
                    </span>
                    <span class="font-mono text-[10px] text-slate-400">EXAMENS CHŪNIN</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 font-mono text-xs">
                    @foreach($otherUpcomingMatches as $match)
                        <div class="p-3 rounded-xl bg-white/5 border border-shinobi-border">
                            <span class="text-[9px] text-shinobi-gold uppercase block mb-1 truncate">{{ $match->tournament?->title }}</span>
                            <div class="flex items-center justify-between font-bold text-white mb-1">
                                <span class="truncate">{{ $match->fighterA?->name }}</span>
                                <span class="text-shinobi-red text-[10px] px-1">vs</span>
                                <span class="truncate">{{ $match->fighterB?->name }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400 block">
                                {{ $match->scheduled_at ? $match->scheduled_at->format('H:i') : 'À venir' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Combat à la Une Allégé -->
        @if($featuredBattle)
            @php
                $fA = $featuredBattle->fighterA;
                $fB = $featuredBattle->fighterB;
                $win = $featuredBattle->winner;
            @endphp
            <div class="rounded-3xl border border-shinobi-border bg-gradient-to-b from-[#1C1318] via-shinobi-card to-shinobi-dark p-6 sm:p-8 shadow-xl">
                <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-3 mb-5">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-shinobi-red animate-ping"></span>
                        <span class="font-mono text-xs font-bold text-shinobi-red uppercase tracking-wider">
                            Dernier Duel Livré dans l'Arène • {{ $featuredBattle->match?->tournament?->title ?? 'Session Spéciale' }}
                        </span>
                    </div>
                    <a href="{{ route('battles.show', $featuredBattle) }}" class="text-xs font-mono text-slate-300 hover:text-white flex items-center gap-1">
                        <span>Revoir l'animation complète</span>
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                    <!-- Fighter A -->
                    <div class="sm:col-span-5 p-4 rounded-2xl {{ $win && $win->id === $fA?->id ? 'bg-shinobi-gold/10 border-2 border-shinobi-gold' : 'bg-shinobi-dark/70 border border-shinobi-border' }} flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl overflow-hidden bg-black shrink-0 border border-white/10">
                            <img src="{{ $fA?->display_avatar_url ?? asset('images/naruto-hero.png') }}" alt="{{ $fA?->name }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='{{ asset('images/naruto-hero.png') }}';">
                        </div>
                        <div class="min-w-0">
                            <span class="text-[9px] font-mono text-slate-400 uppercase">Clan {{ $fA?->clan }}</span>
                            <h4 class="font-serif font-black text-white text-base truncate">{{ $fA?->name }}</h4>
                            @if($win && $win->id === $fA?->id)
                                <span class="badge bg-shinobi-gold text-slate-950 font-mono text-[9px] font-bold">VAINQUEUR</span>
                            @endif
                        </div>
                    </div>

                    <!-- VS Badge -->
                    <div class="sm:col-span-2 text-center">
                        <span class="font-serif font-black text-shinobi-red text-lg">VS</span>
                    </div>

                    <!-- Fighter B -->
                    <div class="sm:col-span-5 p-4 rounded-2xl {{ $win && $win->id === $fB?->id ? 'bg-shinobi-gold/10 border-2 border-shinobi-gold' : 'bg-shinobi-dark/70 border border-shinobi-border' }} flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl overflow-hidden bg-black shrink-0 border border-white/10">
                            <img src="{{ $fB?->display_avatar_url ?? asset('images/naruto-hero.png') }}" alt="{{ $fB?->name }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='{{ asset('images/naruto-hero.png') }}';">
                        </div>
                        <div class="min-w-0">
                            <span class="text-[9px] font-mono text-slate-400 uppercase">Clan {{ $fB?->clan }}</span>
                            <h4 class="font-serif font-black text-white text-base truncate">{{ $fB?->name }}</h4>
                            @if($win && $win->id === $fB?->id)
                                <span class="badge bg-shinobi-gold text-slate-950 font-mono text-[9px] font-bold">VAINQUEUR</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Grille des Combats de la Communauté -->
        <div class="rounded-2xl border border-shinobi-border bg-shinobi-card/80 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-2">
                <span class="font-mono text-xs uppercase font-bold text-white">Tous les Combats de l'Arène</span>
                <span class="font-mono text-[10px] text-slate-400">{{ $communityBattles->total() }} MATCHS ENREGISTRÉS</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 font-mono text-xs">
                @forelse($communityBattles as $b)
                    <a href="{{ route('battles.show', $b) }}" class="p-3.5 rounded-xl bg-white/5 border border-shinobi-border hover:border-shinobi-red/50 transition flex items-center justify-between group text-decoration-none">
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 text-white font-bold truncate">
                                <span>{{ $b->fighterA?->name ?? 'Shinobi' }}</span>
                                <span class="text-shinobi-red text-[10px]">vs</span>
                                <span>{{ $b->fighterB?->name ?? 'Rival' }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400 block mt-1">
                                Vainqueur : <strong class="text-shinobi-gold">{{ $b->winner?->name ?? 'Nul' }}</strong>
                            </span>
                            <span class="text-[9px] text-slate-500 block">
                                {{ $b->match?->tournament?->title ?? 'Arène' }} • {{ $b->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <i class="bi bi-play-circle-fill text-xl text-slate-400 group-hover:text-shinobi-red transition shrink-0 ml-2"></i>
                    </a>
                @empty
                    <div class="col-span-3 py-8 text-center text-slate-400 font-mono text-xs">
                        Aucun match enregistré pour le moment.
                    </div>
                @endforelse
            </div>

            @if($communityBattles->hasPages())
                <div class="pt-3 border-t border-shinobi-border/60">
                    {{ $communityBattles->links() }}
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
