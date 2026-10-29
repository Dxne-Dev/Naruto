@extends('layouts.app')

@section('title', $tournament->title . ' — Direct & Arbre Officiel')

@section('content')
<div class="space-y-6">

    <!-- ========================================================================= -->
    <!-- 1. HERO BANNER : TITRE TOURNOI + STATUT + ACTIONS GM / INSCRIPTION         -->
    <!-- ========================================================================= -->
    <div class="relative overflow-hidden rounded-3xl border border-shinobi-border bg-gradient-to-r from-shinobi-dark via-[#1A1218] to-shinobi-card p-6 sm:p-8 shadow-2xl">
        <div class="pointer-events-none absolute -right-16 -top-16 h-80 w-80 rounded-full bg-shinobi-red/15 blur-3xl"></div>
        <div class="pointer-events-none absolute left-0 top-0 h-full w-48 bg-gradient-to-r from-shinobi-red/10 to-transparent"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('tournaments.open-list') }}" class="inline-flex items-center gap-1 rounded-full border border-shinobi-border bg-white/5 px-3 py-0.5 font-mono text-[10px] text-slate-300 hover:text-white hover:bg-white/10 transition">
                        <i class="bi bi-chevron-left"></i> Retour aux Tournois
                    </a>
                    
                    @if($tournament->status === 'registration')
                        <span class="rounded-full bg-emerald-500/10 border border-emerald-500/30 px-3 py-0.5 font-mono text-[10px] font-bold text-emerald-400 uppercase">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse mr-1"></span> Inscriptions Ouvertes
                        </span>
                    @elseif($tournament->status === 'running')
                        <span class="rounded-full bg-shinobi-red/15 border border-shinobi-red/40 px-3 py-0.5 font-mono text-[10px] font-bold text-shinobi-red uppercase">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-shinobi-red animate-ping mr-1"></span> Combats en Direct
                        </span>
                    @elseif($tournament->status === 'locked')
                        <span class="rounded-full bg-shinobi-gold/15 border border-shinobi-gold/40 px-3 py-0.5 font-mono text-[10px] font-bold text-shinobi-gold uppercase">
                            <i class="bi bi-lock-fill mr-1"></i> Arbre Verrouillé
                        </span>
                    @elseif($tournament->status === 'completed')
                        <span class="rounded-full bg-white/10 border border-white/20 px-3 py-0.5 font-mono text-[10px] font-bold text-slate-300 uppercase inline-flex items-center gap-1">
                            <i class="bi bi-check-lg text-emerald-400"></i> <span>Tournoi Terminé</span>
                        </span>
                    @endif

                    <span class="font-mono text-[10px] text-slate-400">SESSION #{{ $tournament->id }}</span>
                </div>

                <h1 class="font-serif text-3xl sm:text-4xl font-black uppercase tracking-tight text-white">
                    {{ $tournament->title }}
                </h1>

                <!-- Métadonnées Dynamiques -->
                <div class="flex flex-wrap items-center gap-4 text-xs font-mono text-slate-300 pt-1">
                    <span>
                        <strong class="text-white">{{ $tournament->entries->count() }}</strong>/8 Combattants
                    </span>
                    <span>•</span>
                    <span>
                        Format : <span class="text-slate-200">Élimination directe (3 Tours)</span>
                    </span>
                    <span>•</span>
                    <span>
                        Récompense : <span class="text-shinobi-gold font-bold">+300 XP & Titre Chūnin</span>
                    </span>
                    @if($tournament->registration_closes_at && $tournament->status === 'registration')
                        <span>•</span>
                        <span class="text-emerald-400">
                            Clôture : {{ $tournament->registration_closes_at->translatedFormat('H:i') }} ({{ $tournament->registration_closes_at->diffForHumans() }})
                        </span>
                    @elseif($tournament->starts_at)
                        <span>•</span>
                        <span class="text-slate-300">
                            Début : {{ $tournament->starts_at->translatedFormat('D d M à H:i') }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Actions Utilisateur & Maître du Jeu -->
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                @auth
                    @if($tournament->status === 'registration')
                        @if(!$userFighter)
                            <a href="{{ route('onboarding.index') }}" class="rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white px-4 py-2.5 font-mono text-xs font-bold shadow-lg shadow-shinobi-red/25 transition">
                                Forger un shinobi pour s'inscrire ▸
                            </a>
                        @elseif($isRegistered)
                            <form method="POST" action="{{ route('tournaments.unregister', $tournament) }}">
                                @csrf
                                <button type="submit" class="rounded-xl border border-red-500/50 bg-red-950/40 hover:bg-red-900/60 text-red-300 px-4 py-2.5 font-mono text-xs font-bold transition">
                                    Se désinscrire
                                </button>
                            </form>
                        @elseif($tournament->entries->count() < 8)
                            <form method="POST" action="{{ route('tournaments.register', $tournament) }}">
                                @csrf
                                <button type="submit" class="rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white px-5 py-2.5 font-mono text-xs font-bold shadow-lg shadow-shinobi-red/25 transition">
                                    Inscrire {{ $userFighter->name }} ▸
                                </button>
                            </form>
                        @else
                            <span class="rounded-xl border border-shinobi-border bg-white/5 text-slate-400 px-4 py-2.5 font-mono text-xs">
                                Tournoi complet (8/8)
                            </span>
                        @endif
                    @endif

                    @if(auth()->user()->isGameMaster())
                        <a href="{{ route('admin.tournaments.edit', $tournament) }}" class="rounded-xl border border-shinobi-gold/50 bg-shinobi-gold/10 hover:bg-shinobi-gold/20 text-shinobi-gold px-4 py-2.5 font-mono text-xs font-bold transition">
                            <i class="bi bi-gear-fill mr-1"></i> Console Admin GM
                        </a>
                    @endif
                @endauth
            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. GRILLE DES 8 PARTICIPANTS INSCRITS (Cards Shinobi Dark)                 -->
    <!-- ========================================================================= -->
    <div class="glass3d rounded-2xl border border-shinobi-border/70 p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-shinobi-border/60 pb-3">
            <div>
                <span class="font-mono text-[10px] font-bold uppercase tracking-wider text-shinobi-red">TABLEAU DES ENGAGÉS</span>
                <h3 class="font-serif text-xl font-black uppercase text-white">
                    Shinobis Inscrits ({{ $tournament->entries->count() }}/8)
                </h3>
            </div>
            <span class="font-mono text-xs text-slate-400">
                {{ 8 - $tournament->entries->count() > 0 ? (8 - $tournament->entries->count()) . ' place(s) vacante(s)' : 'Complet (Prêt au combat)' }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @for($i = 0; $i < 8; $i++)
                @php $entry = $tournament->entries->get($i); @endphp
                @if($entry && $entry->fighter)
                    @php $f = $entry->fighter; @endphp
                    <div class="rounded-xl border {{ $entry->final_position === 1 ? 'border-shinobi-gold/80 bg-shinobi-gold/10 ring-1 ring-shinobi-gold/30' : 'border-shinobi-border bg-white/5' }} p-3 flex items-center gap-3 transition">
                        <div class="w-12 h-12 rounded-xl overflow-hidden border border-shinobi-border bg-shinobi-dark shrink-0">
                            @if($f->portrait_path)
                                <img src="{{ asset($f->portrait_path) }}" alt="{{ $f->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center font-serif text-base font-bold text-shinobi-gold">
                                    {{ substr($f->name, 0, 2) }}
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-1">
                                <span class="font-serif font-bold text-sm text-white truncate">{{ $f->name }}</span>
                                @if($entry->final_position === 1)
                                    <span class="text-shinobi-gold text-xs shrink-0" title="Champion du tournoi"><i class="bi bi-trophy-fill"></i></span>
                                @endif
                            </div>
                            <div class="font-mono text-[11px] text-slate-400 truncate">
                                Clan {{ $f->clan }} • {{ $f->village }}
                            </div>
                            <div class="flex items-center gap-2 mt-0.5 font-mono text-[10px] text-slate-500">
                                <span>Niv.{{ $f->level }}</span>
                                <span>•</span>
                                <span class="text-slate-400 truncate">{{ $f->user?->username }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-shinobi-border/60 bg-white/[0.02] p-3 flex items-center justify-center min-h-[66px] text-center">
                        <span class="font-mono text-xs text-slate-500">
                            Slot 0{{ $i + 1 }} • En attente
                        </span>
                    </div>
                @endif
            @endfor
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. GRAND ARBRE ÉLIMINATOIRE DE COMBAT (BRACKET DARK SHŌNEN)               -->
    <!-- ========================================================================= -->
    <div class="glass3d rounded-2xl border border-shinobi-border/70 p-5 sm:p-6 space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-shinobi-border/60 pb-3">
            <div>
                <span class="font-mono text-[10px] font-bold uppercase tracking-wider text-shinobi-red">ARBORESCENCE DU TOURNOI</span>
                <h3 class="font-serif text-xl font-black uppercase text-white">
                    Tableau Officiel des Duels
                </h3>
            </div>
            <span class="font-mono text-xs text-slate-400">
                {{ $tournament->matches->whereNotNull('winner_id')->count() }}/{{ $tournament->matches->count() }} combat(s) résolu(s)
            </span>
        </div>

        @if($tournament->matches->isEmpty())
            <div class="text-center py-12 space-y-3 font-mono text-xs text-slate-400">
                <i class="bi bi-diagram-3 text-3xl text-slate-500 block"></i>
                <p>Le tableau officiel sera généré dès la clôture des inscriptions ou la validation des 8 participants.</p>
                <span class="inline-block rounded-lg border border-shinobi-border bg-white/5 px-3 py-1 text-slate-300">
                    Les 8 combattants seront tirés au sort selon leur ordre d'inscription.
                </span>
            </div>
        @else
            <!-- Grille du Bracket (Quarts -> Demis -> Finale -> Vainqueur) avec scroll horizontal sur mobile -->
            <div class="overflow-x-auto pb-4">
                <div class="min-w-[760px] grid grid-cols-4 gap-4 items-center relative py-4">
                    
                    @php
                        $round3Match = $round3Matches->first();
                        $winner = $round3Match?->winner;
                    @endphp

                    <!-- COLONNE 1 : 1/4 DE FINALE (4 Matchs) -->
                    <div class="space-y-4">
                        <div class="text-center font-mono text-[10px] uppercase font-bold text-slate-400 mb-2 border-b border-shinobi-border/40 pb-1">
                            1/4 de Finale
                        </div>
                        @for($i = 0; $i < 4; $i++)
                            @php $m = $round1Matches->get($i); @endphp
                            <div class="rounded-xl border {{ $m && $m->winner_id ? 'border-shinobi-gold/60 bg-shinobi-dark' : 'border-shinobi-border bg-white/5' }} p-3 space-y-2 font-mono text-xs shadow-sm">
                                <div class="flex items-center justify-between text-[10px] text-slate-500 pb-1 border-b border-white/5">
                                    <span>DUEL 0{{ $i + 1 }}</span>
                                    <span class="uppercase {{ $m && $m->status === 'completed' ? 'text-emerald-400' : 'text-slate-400' }}">{{ $m?->status ?? 'En attente' }}</span>
                                </div>
                                <!-- Fighter A -->
                                <div class="flex items-center justify-between gap-1.5 p-1 rounded {{ $m && $m->winner_id === $m->fighter_a_id ? 'bg-shinobi-gold/15 text-shinobi-gold font-bold' : 'text-slate-300' }}">
                                    <span class="truncate text-[11px]">{{ $m?->fighterA?->name ?? 'Participant ' . (($i * 2) + 1) }}</span>
                                    @if($m && $m->winner_id === $m->fighter_a_id)
                                        <i class="bi bi-trophy-fill text-[10px] text-shinobi-gold shrink-0"></i>
                                    @endif
                                </div>
                                <!-- Fighter B -->
                                <div class="flex items-center justify-between gap-1.5 p-1 rounded {{ $m && $m->winner_id === $m->fighter_b_id ? 'bg-shinobi-gold/15 text-shinobi-gold font-bold' : 'text-slate-300' }}">
                                    <span class="truncate text-[11px]">{{ $m?->fighterB?->name ?? 'Participant ' . (($i * 2) + 2) }}</span>
                                    @if($m && $m->winner_id === $m->fighter_b_id)
                                        <i class="bi bi-trophy-fill text-[10px] text-shinobi-gold shrink-0"></i>
                                    @endif
                                </div>
                                @if($m && $m->battle)
                                    <a href="{{ route('battles.show', $m->battle) }}" class="block text-center rounded-lg border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-300 text-[10px] py-1 transition">
                                        Revoir le duel ▸
                                    </a>
                                @endif
                            </div>
                        @endfor
                    </div>

                    <!-- COLONNE 2 : 1/2 FINALE (2 Matchs) -->
                    <div class="space-y-16 my-auto">
                        <div class="text-center font-mono text-[10px] uppercase font-bold text-slate-400 mb-2 border-b border-shinobi-border/40 pb-1">
                            1/2 Finale
                        </div>
                        @for($i = 0; $i < 2; $i++)
                            @php $m = $round2Matches->get($i); @endphp
                            <div class="rounded-xl border {{ $m && $m->winner_id ? 'border-shinobi-gold/60 bg-shinobi-dark' : 'border-shinobi-border bg-white/5' }} p-3 space-y-2 font-mono text-xs shadow-sm">
                                <div class="flex items-center justify-between text-[10px] text-slate-500 pb-1 border-b border-white/5">
                                    <span>DEMI 0{{ $i + 1 }}</span>
                                    <span class="uppercase {{ $m && $m->status === 'completed' ? 'text-emerald-400' : 'text-slate-400' }}">{{ $m?->status ?? 'En attente' }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-1.5 p-1 rounded {{ $m && $m->winner_id === $m->fighter_a_id ? 'bg-shinobi-gold/15 text-shinobi-gold font-bold' : 'text-slate-300' }}">
                                    <span class="truncate text-[11px]">{{ $m?->fighterA?->name ?? 'Vainqueur QF' }}</span>
                                    @if($m && $m->winner_id === $m->fighter_a_id)
                                        <i class="bi bi-trophy-fill text-[10px] text-shinobi-gold shrink-0"></i>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between gap-1.5 p-1 rounded {{ $m && $m->winner_id === $m->fighter_b_id ? 'bg-shinobi-gold/15 text-shinobi-gold font-bold' : 'text-slate-300' }}">
                                    <span class="truncate text-[11px]">{{ $m?->fighterB?->name ?? 'Vainqueur QF' }}</span>
                                    @if($m && $m->winner_id === $m->fighter_b_id)
                                        <i class="bi bi-trophy-fill text-[10px] text-shinobi-gold shrink-0"></i>
                                    @endif
                                </div>
                                @if($m && $m->battle)
                                    <a href="{{ route('battles.show', $m->battle) }}" class="block text-center rounded-lg border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-300 text-[10px] py-1 transition">
                                        Revoir la demi ▸
                                    </a>
                                @endif
                            </div>
                        @endfor
                    </div>

                    <!-- COLONNE 3 : FINALE (1 Match) -->
                    <div class="my-auto space-y-3">
                        <div class="text-center font-mono text-[10px] uppercase font-bold text-slate-400 mb-2 border-b border-shinobi-border/40 pb-1">
                            Finale du Titre
                        </div>
                        <div class="rounded-xl border {{ $round3Match && $round3Match->winner_id ? 'border-shinobi-gold/80 bg-shinobi-dark ring-2 ring-shinobi-gold/20' : 'border-shinobi-border bg-white/5' }} p-3.5 space-y-2 font-mono text-xs shadow-lg">
                            <div class="flex items-center justify-between text-[10px] text-slate-500 pb-1 border-b border-white/5">
                                <span class="text-shinobi-gold font-bold">MATCH DÉCISIF</span>
                                <span class="uppercase {{ $round3Match && $round3Match->status === 'completed' ? 'text-emerald-400' : 'text-slate-400' }}">{{ $round3Match?->status ?? 'En attente' }}</span>
                            </div>
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
                            @if($round3Match && $round3Match->battle)
                                <a href="{{ route('battles.show', $round3Match->battle) }}" class="block text-center rounded-lg bg-shinobi-gold/20 hover:bg-shinobi-gold/30 border border-shinobi-gold/40 text-shinobi-gold font-bold text-[10px] py-1.5 transition">
                                    Visionner la finale ▸
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- COLONNE 4 : VAINQUEUR / PODIUM CHŪNIN -->
                    <div class="my-auto text-center space-y-3">
                        <div class="relative inline-block">
                            <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-shinobi-gold/20 via-shinobi-gold/10 to-transparent border-2 border-shinobi-gold flex items-center justify-center text-shinobi-gold text-2xl shadow-xl shadow-shinobi-gold/20">
                                <i class="bi bi-trophy-fill text-2xl"></i>
                            </div>
                            <span class="block font-serif font-black text-xs uppercase tracking-wider text-shinobi-gold mt-2">
                                Champion Titré
                            </span>
                        </div>

                        <div class="rounded-xl border border-shinobi-gold/40 bg-shinobi-card p-3 shadow-lg">
                            <h4 class="font-serif font-bold text-sm text-white truncate">
                                {{ $winner?->name ?? 'En attente...' }}
                            </h4>
                            <span class="font-mono text-[10px] text-slate-400 block mt-0.5">
                                {{ $winner ? 'Clan ' . $winner->clan . ' • ' . $winner->village : 'Titre Chūnin' }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        @endif

    </div>

</div>
@endsection
