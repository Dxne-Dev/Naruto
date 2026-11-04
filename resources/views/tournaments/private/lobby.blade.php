@extends('layouts.app')

@section('title', 'Salon Privé — ' . $tournament->title)

@section('og_meta')
    <meta property="og:site_name" content="Otaku Arena">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $challengeTitle }}">
    <meta property="og:description" content="Rejoins le salon privé « {{ $tournament->title }} » (Code : {{ $tournament->room_code }}) sur Otaku Arena. Révèle ton nindô et combats pour la victoire !">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:url" content="{{ $shareUrl }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $challengeTitle }}">
    <meta name="twitter:description" content="Rejoins le salon privé « {{ $tournament->title }} » (Code : {{ $tournament->room_code }}) sur Otaku Arena !">
    <meta name="twitter:image" content="{{ $ogImage }}">
@endsection

@section('content')
<div class="space-y-6" x-data="{
    copiedType: null,
    shareUrl: '{{ $shareUrl }}',
    shareCode: '{{ $tournament->room_code }}',
    shareTitle: @js($challengeTitle),
    shareMessage: @js('⚔️ ' . $challengeTitle . "\n\nRejoins mon salon privé « " . $tournament->title . " » sur Otaku Arena !\nCode de combat : " . $tournament->room_code . "\nLien direct : " . $shareUrl),
    copyText(text, type) {
        navigator.clipboard.writeText(text);
        this.copiedType = type;
        setTimeout(() => this.copiedType = null, 2500);
    },
    async shareNative() {
        if (navigator.share) {
            try {
                await navigator.share({
                    title: this.shareTitle,
                    text: this.shareMessage,
                    url: this.shareUrl
                });
            } catch (err) {
                if (err.name !== 'AbortError') {
                    this.copyText(this.shareMessage, 'all');
                }
            }
        } else {
            this.copyText(this.shareMessage, 'all');
        }
    },
    shareWhatsApp() {
        const url = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(this.shareMessage);
        window.open(url, '_blank');
    }
}">

    <!-- HERO HEADER : SALON PRIVÉ -->
    <div class="relative overflow-hidden rounded-3xl border border-shinobi-border bg-gradient-to-r from-shinobi-dark via-[#1A1218] to-shinobi-card p-6 sm:p-8 shadow-2xl">
        <div class="pointer-events-none absolute -right-16 -top-16 h-80 w-80 rounded-full bg-shinobi-red/15 blur-3xl"></div>
        <div class="pointer-events-none absolute left-0 top-0 h-full w-48 bg-gradient-to-r from-shinobi-red/10 to-transparent"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full border border-shinobi-red/30 bg-shinobi-red/10 px-3 py-0.5 font-mono text-[10px] font-bold text-shinobi-red uppercase">
                        SALON PRIVÉ • {{ $tournament->max_participants }} JOUEURS
                    </span>
                    @if($tournament->status === 'completed')
                        <span class="rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 px-2.5 py-0.5 font-mono text-[10px] font-bold">
                            Tournoi Terminé
                        </span>
                    @elseif($tournament->status === 'registration')
                        <span class="rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-300 px-2.5 py-0.5 font-mono text-[10px] font-bold animate-pulse">
                            Salle d'Attente (Lobby)
                        </span>
                    @endif
                </div>

                <h1 class="font-serif text-2xl sm:text-3xl font-black uppercase tracking-tight text-white">
                    {{ $tournament->title }}
                </h1>

                <!-- Message de défi affiché fièrement -->
                <p class="font-mono text-xs text-amber-300/90 flex items-center gap-1.5 font-semibold">
                    <i class="bi bi-swords text-shinobi-gold"></i>
                    <span>{{ $challengeTitle }}</span>
                </p>

                <p class="font-mono text-xs text-slate-300">
                    Créé par <strong class="text-white">{{ $creatorShinobiName }}</strong> • 
                    Places : <strong class="text-shinobi-gold">{{ $currentCount }} / {{ $max }}</strong>
                </p>
            </div>

            <!-- CARTE D'INVITATION & PARTAGE MULTI-CANAL -->
            <div class="rounded-2xl border-2 border-shinobi-gold/60 bg-black/70 p-5 space-y-3.5 font-mono text-xs max-w-md w-full shadow-2xl shrink-0">
                <div class="flex items-center justify-between">
                    <span class="text-slate-300 font-bold uppercase text-[10px] flex items-center gap-1.5">
                        <i class="bi bi-shield-lock-fill text-shinobi-gold"></i>
                        Code secret du salon
                    </span>
                    <span x-show="copiedType" 
                          x-transition
                          class="text-emerald-400 font-bold text-[10px] flex items-center gap-1">
                        <i class="bi bi-check2-circle"></i>
                        <span x-text="copiedType === 'code' ? 'Code copié !' : (copiedType === 'link' ? 'Lien copié !' : 'Invitation copiée !')"></span>
                    </span>
                </div>

                <!-- Code display + copy -->
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-shinobi-dark border border-shinobi-border rounded-xl px-4 py-2 text-center text-xl font-black font-mono tracking-widest text-shinobi-gold select-all shadow-inner">
                        {{ $tournament->room_code }}
                    </div>
                    <button type="button" @click="copyText(shareCode, 'code')" 
                            class="px-3.5 py-2.5 rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 text-white font-bold transition flex items-center gap-1"
                            title="Copier uniquement le code">
                        <i class="bi bi-clipboard text-base"></i>
                    </button>
                </div>

                <!-- Boutons de partage vers les amis -->
                <div class="space-y-2 pt-1">
                    <!-- WhatsApp Share (Message complet + lien) -->
                    <button type="button" @click="shareWhatsApp()"
                            class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-500 hover:to-green-500 text-white font-bold text-[11px] shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                        <i class="bi bi-whatsapp text-sm"></i>
                        <span>Défier mes amis sur WhatsApp</span>
                    </button>

                    <div class="grid grid-cols-2 gap-2">
                        <!-- Native Share API (Mobile / Desktop) -->
                        <button type="button" @click="shareNative()"
                                class="py-2 px-2.5 rounded-xl bg-white/10 hover:bg-white/15 border border-shinobi-border text-slate-200 hover:text-white font-mono text-[10px] font-bold transition flex items-center justify-center gap-1.5">
                            <i class="bi bi-share-fill text-xs text-shinobi-gold"></i>
                            <span>Partager l'invit</span>
                        </button>

                        <!-- Copier message d'invitation complet -->
                        <button type="button" @click="copyText(shareMessage, 'all')"
                                class="py-2 px-2.5 rounded-xl bg-white/10 hover:bg-white/15 border border-shinobi-border text-slate-200 hover:text-white font-mono text-[10px] font-bold transition flex items-center justify-center gap-1.5"
                                title="Copier le texte du défi + lien">
                            <i class="bi bi-chat-quote-fill text-xs text-shinobi-red"></i>
                            <span>Copier le texte</span>
                        </button>
                    </div>

                    <!-- Copier lien direct -->
                    <button type="button" @click="copyText(shareUrl, 'link')"
                            class="w-full py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-shinobi-border/60 text-slate-400 hover:text-white font-mono text-[10px] transition flex items-center justify-center gap-1.5">
                        <i class="bi bi-link-45deg text-sm"></i>
                        <span>Copier juste le lien direct</span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- ÉTAT DES PARTICIPANTS (LOBBY) -->
    <div class="rounded-2xl border border-shinobi-border bg-shinobi-card p-6 space-y-5 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-shinobi-border/60 pb-4">
            <div>
                <h3 class="font-serif font-black uppercase text-base text-white tracking-wide flex items-center gap-2">
                    <i class="bi bi-people-fill text-shinobi-red"></i>
                    <span>Combattants Réunis ({{ $currentCount }} / {{ $max }})</span>
                </h3>
                <p class="font-mono text-xs text-slate-400">
                    @if($isFull)
                        Tous les shinobis sont prêts ! Le tournoi peut commencer.
                    @else
                        En attente de {{ $max - $currentCount }} combattant(s) supplémentaire(s)...
                    @endif
                </p>
            </div>

            <!-- BOUTON DE LANCEMENT (CRÉATEUR UNIQUEMENT) -->
            @if($tournament->status === 'registration')
                @if($isCreator)
                    @if($isFull)
                        <form action="{{ route('tournaments.private.start', $tournament) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-serif font-black uppercase text-xs tracking-wider shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2">
                                <i class="bi bi-play-circle-fill text-base"></i>
                                <span>Lancer le Tournoi</span>
                            </button>
                        </form>
                    @else
                        <button type="button" disabled class="px-5 py-2.5 rounded-xl bg-white/5 border border-white/10 text-slate-500 font-mono text-xs cursor-not-allowed">
                            En attente du quota ({{ $currentCount }}/{{ $max }})
                        </button>
                    @endif
                @else
                    <span class="font-mono text-xs text-slate-400 italic">
                        En attente que le créateur lance le combat...
                    </span>
                @endif
            @endif
        </div>

        <!-- GRILLE DES COMBATTANTS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($tournament->entries as $entry)
                @php
                    $f = $entry->fighter;
                    $isRoomCreator = $f?->user_id === $tournament->creator_id;
                @endphp
                <div class="relative rounded-2xl border border-shinobi-border bg-shinobi-dark p-4 flex items-center gap-3 shadow-lg">
                    <div class="relative w-14 h-14 rounded-xl overflow-hidden border border-shinobi-border shrink-0 bg-black">
                        <img src="{{ $f?->portrait_path ?? ($f?->source_photo_path ?? asset('images/naruto-hero.png')) }}" 
                             alt="{{ $f?->name }}" 
                             class="w-full h-full object-cover object-top">
                        <span class="absolute bottom-0 right-0 bg-shinobi-red text-white font-mono text-[9px] font-bold px-1 rounded-tl">
                            N.{{ $f?->level ?? 1 }}
                        </span>
                    </div>

                    <div class="min-w-0 space-y-0.5">
                        <div class="flex items-center gap-1.5">
                            <h4 class="font-serif font-bold text-sm text-white truncate">{{ $f?->name }}</h4>
                            @if($isRoomCreator)
                                <span class="text-amber-400" title="Créateur du salon"><i class="bi bi-star-fill text-xs"></i></span>
                            @endif
                        </div>
                        <div class="font-mono text-[11px] text-slate-400">
                            {{ $f?->clan ?? 'Clan' }} • <span class="text-shinobi-gold font-semibold">{{ $f?->primary_element ?? 'Vent' }}</span>
                        </div>
                        <div class="font-mono text-[10px] text-slate-500 truncate">
                            Joueur : {{ $f?->user?->name ?? 'Anonyme' }}
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- PLACES VIDES RESTANTES -->
            @for($i = $currentCount; $i < $max; $i++)
                <div class="rounded-2xl border-2 border-dashed border-white/10 bg-black/20 p-4 flex items-center justify-center gap-3 text-slate-600 font-mono text-xs min-h-[86px]">
                    <i class="bi bi-person-plus text-lg"></i>
                    <span>Place libre #{{ $i + 1 }}</span>
                </div>
            @endfor
        </div>
    </div>

    <!-- ARBRE DES MATCHS (SI LE TOURNOI A ÉTÉ LANCÉ / COMPLÉTÉ) -->
    @if($tournament->matches->isNotEmpty())
    <div class="rounded-2xl border border-shinobi-border bg-shinobi-card p-6 space-y-6 shadow-xl">
        <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-3 font-mono text-xs">
            <h3 class="font-serif font-black uppercase text-base text-white tracking-wide flex items-center gap-2">
                <i class="bi bi-diagram-3-fill text-shinobi-gold"></i>
                <span>Arbre des Combats & Résultats</span>
            </h3>
            <span class="text-slate-400">{{ $tournament->matches->count() }} match(s)</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($tournament->matches as $match)
                @php
                    $battle = \App\Models\Battle::where('match_id', $match->id)->first();
                    $isSemi = ($tournament->max_participants === 4 && $match->round === 1) || ($tournament->max_participants === 8 && $match->round === 2);
                    $isFinal = ($tournament->max_participants === 4 && $match->round === 2) || ($tournament->max_participants === 8 && $match->round === 3);
                    $roundTitle = $isFinal ? '🏆 GRANDE FINALE' : ($isSemi ? '⚡ DEMI-FINALE' : 'QUART DE FINALE');
                @endphp
                <div class="rounded-xl border border-shinobi-border/80 bg-shinobi-dark/90 p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-2 font-mono text-[10px]">
                        <span class="font-bold text-shinobi-gold">{{ $roundTitle }}</span>
                        <span class="text-slate-400">Match #{{ $match->match_number }}</span>
                    </div>

                    <div class="space-y-2 font-mono text-xs">
                        <!-- Combattant A -->
                        <div class="flex items-center justify-between p-2 rounded-lg {{ $match->winner_id === $match->fighter_a_id ? 'bg-emerald-950/40 border border-emerald-500/40 text-emerald-300 font-bold' : 'bg-black/40 text-slate-300' }}">
                            <span class="truncate">{{ $match->fighterA?->name ?? 'En attente' }}</span>
                            @if($match->winner_id === $match->fighter_a_id)
                                <i class="bi bi-trophy-fill text-shinobi-gold"></i>
                            @endif
                        </div>

                        <!-- VS -->
                        <div class="text-center font-mono text-[9px] text-slate-500 uppercase tracking-widest font-bold">VS</div>

                        <!-- Combattant B -->
                        <div class="flex items-center justify-between p-2 rounded-lg {{ $match->winner_id === $match->fighter_b_id ? 'bg-emerald-950/40 border border-emerald-500/40 text-emerald-300 font-bold' : 'bg-black/40 text-slate-300' }}">
                            <span class="truncate">{{ $match->fighterB?->name ?? 'En attente' }}</span>
                            @if($match->winner_id === $match->fighter_b_id)
                                <i class="bi bi-trophy-fill text-shinobi-gold"></i>
                            @endif
                        </div>
                    </div>

                    @if($battle)
                        <div class="pt-2 border-t border-shinobi-border/40">
                            <a href="{{ route('battles.show', $battle) }}" class="w-full py-1.5 rounded-lg bg-shinobi-red/20 hover:bg-shinobi-red/30 border border-shinobi-red/40 text-shinobi-red font-bold font-mono text-[11px] transition flex items-center justify-center gap-1.5 text-decoration-none">
                                <i class="bi bi-play-fill text-base"></i>
                                <span>Voir le combat & Replay</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="flex items-center justify-between font-mono text-xs pt-2">
        <a href="{{ route('tournaments.private.index') }}" class="px-4 py-2 rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-300 font-bold transition flex items-center gap-2 text-decoration-none">
            <i class="bi bi-arrow-left"></i>
            <span>Retour aux Salons Privés</span>
        </a>
    </div>

</div>
@endsection
