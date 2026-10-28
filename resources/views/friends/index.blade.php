@extends('layouts.app')

@section('title', 'Mes Amis & Rivaux — Otaku Arena')

@section('content')
<div class="space-y-6 pb-12">

    @php
        $activeTab = request()->query('tab', 'friends');
        $hasSidebarContent = $pendingChallenges->isNotEmpty() || $recentActivities->isNotEmpty();
    @endphp

    <!-- ========================================================================= -->
    <!-- 1. EN-TÊTE « MES AMIS & RIVAUX »                                          -->
    <!-- ========================================================================= -->
    <div class="relative overflow-hidden rounded-3xl border border-shinobi-border bg-gradient-to-r from-shinobi-dark via-[#1A1218] to-shinobi-card p-6 sm:p-8 shadow-2xl">
        <div class="pointer-events-none absolute -right-12 -top-12 h-64 w-64 rounded-full bg-shinobi-red/15 blur-3xl"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="font-serif text-3xl sm:text-4xl font-black uppercase text-white tracking-tight">
                        Mes Amis & Rivaux
                    </h1>
                    <span class="rounded-full bg-shinobi-red/20 border border-shinobi-red/40 px-3 py-0.5 font-mono text-xs font-bold text-shinobi-red">
                        {{ $friends->count() }} ninja{{ $friends->count() > 1 ? 's' : '' }}
                    </span>
                </div>
                <p class="font-mono text-xs text-slate-400 mt-1">
                    Échange, défie, progresse. Le lien entre shinobis naît aussi du combat.
                </p>
            </div>

            <!-- Formulaire de recherche joueur & Bouton Ajouter -->
            <form method="GET" action="{{ route('friends.index') }}" class="flex items-center gap-2">
                <div class="relative min-w-[200px] sm:min-w-[260px]">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-500 text-xs">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Rechercher un ninja..." 
                           class="w-full rounded-xl border border-shinobi-border bg-shinobi-dark/80 pl-8 pr-3 py-2 font-mono text-xs text-white placeholder-slate-500 focus:border-shinobi-red focus:outline-none focus:ring-1 focus:ring-shinobi-red transition">
                </div>
                <button type="button" onclick="document.getElementById('addFriendModal').classList.toggle('hidden')" class="rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white px-3.5 py-2 font-mono text-xs font-bold transition flex items-center gap-1.5 shadow-lg shadow-shinobi-red/25 shrink-0">
                    <i class="bi bi-plus-lg"></i>
                    <span class="hidden sm:inline">Ajouter</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Modal d'ajout d'ami -->
    <div id="addFriendModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="glass3d rounded-3xl border border-shinobi-border p-6 max-w-md w-full space-y-4">
            <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-3">
                <h3 class="font-serif font-bold text-lg text-white">Ajouter un Rival</h3>
                <button type="button" onclick="document.getElementById('addFriendModal').classList.add('hidden')" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
            </div>
            <form method="POST" action="{{ route('friends.request') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block font-mono text-xs text-slate-300 mb-1">PSEUDONYME DU NINJA</label>
                    <input type="text" name="friend_username" required placeholder="Ex: Sasuke99" class="w-full rounded-xl border border-shinobi-border bg-shinobi-dark px-3.5 py-2.5 font-mono text-xs text-white focus:border-shinobi-red focus:outline-none">
                </div>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-shinobi-red text-white font-mono text-xs font-bold hover:bg-shinobi-red-hover transition">
                    ENVOYER UNE DEMANDE ▸
                </button>
            </form>
        </div>
    </div>

    <!-- Résultats de recherche si recherche active -->
    @if(isset($searchResults) && $searchResults->isNotEmpty())
        <div class="glass3d rounded-2xl border border-shinobi-border/70 p-5 space-y-3">
            <span class="font-mono text-xs font-bold uppercase text-slate-400">Résultats pour « {{ request('q') }} »</span>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($searchResults as $res)
                    <div class="p-3 rounded-xl bg-white/5 border border-shinobi-border flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-shinobi-dark border border-shinobi-border overflow-hidden shrink-0">
                                <img src="{{ $res->fighter?->portrait_path ?? ($res->fighter?->source_photo_path ?? asset('images/naruto-hero.png')) }}" alt="{{ $res->username }}" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0">
                                <span class="font-mono text-xs font-bold text-white truncate block">{{ $res->username }}</span>
                                <span class="font-mono text-[10px] text-slate-400">{{ $res->fighter?->name ?? 'Aucun combattant' }}</span>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('friends.request') }}">
                            @csrf
                            <input type="hidden" name="friend_id" value="{{ $res->id }}">
                            <button type="submit" class="p-2 rounded-lg bg-shinobi-red text-white text-xs hover:bg-shinobi-red-hover transition" title="Ajouter en ami">
                                <i class="bi bi-person-plus-fill"></i>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 2. ONGLETS SECONDAIRES DYNAMIQUES AVEC COMPTEURS COMPLETS                  -->
    <!-- ========================================================================= -->
    <div class="flex items-center gap-2 border-b border-shinobi-border/60 pb-3 font-mono text-xs overflow-x-auto">
        <a href="{{ route('friends.index', ['tab' => 'friends']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl transition font-bold shrink-0 {{ $activeTab === 'friends' ? 'bg-shinobi-red text-white shadow-lg shadow-shinobi-red/25' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <i class="bi bi-people-fill text-sm"></i>
            <span>Amis ({{ $friends->count() }})</span>
        </a>
        <a href="{{ route('friends.index', ['tab' => 'requests']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl transition font-bold shrink-0 {{ $activeTab === 'requests' ? 'bg-shinobi-red text-white shadow-lg shadow-shinobi-red/25' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <i class="bi bi-hourglass-split text-sm"></i>
            <span>Demandes en attente ({{ $pendingReceived->count() }})</span>
        </a>
        <a href="{{ route('friends.index', ['tab' => 'challenges']) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl transition font-bold shrink-0 {{ $activeTab === 'challenges' ? 'bg-shinobi-red text-white shadow-lg shadow-shinobi-red/25' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <i class="bi bi-lightning-charge-fill text-sm"></i>
            <span>Défis reçus ({{ $pendingChallenges->count() }})</span>
        </a>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. GRILLE PRINCIPALE (SIDEBAR MASQUÉE PAR DÉFAUT SI AUCUNE DONNÉE RÉELLE)  -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 {{ $hasSidebarContent ? 'lg:grid-cols-12' : 'grid-cols-1' }} gap-6 items-start">

        <!-- ===================================================================== -->
        <!-- ZONE PRINCIPALE (8 Cols si Sidebar active, 12 Cols pleine largeur)    -->
        <!-- ===================================================================== -->
        <div class="{{ $hasSidebarContent ? 'lg:col-span-8' : 'w-full' }} space-y-3">
            
            @if($activeTab === 'friends')
                @forelse($friends as $friendUser)
                    @php
                        $friendFighter = $friendUser->fighter;
                        $isOnline = isset($onlineUserIds[$friendUser->id]);
                    @endphp
                    <div class="group rounded-2xl border border-shinobi-border bg-shinobi-card/85 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-shinobi-red/50 hover:bg-[#1A161E] transition-all duration-200 shadow-md">
                        
                        <!-- Info Ninja -->
                        <div class="flex items-center gap-4 min-w-0">
                            <!-- Avatar circulaire avec pastille de vérité absolue (En ligne / Hors ligne) -->
                            <div class="relative w-12 h-12 sm:w-14 sm:h-14 rounded-full border-2 {{ $isOnline ? 'border-emerald-500/80 shadow-[0_0_12px_rgba(16,185,129,0.3)]' : 'border-shinobi-border' }} overflow-hidden shrink-0 bg-black">
                                <img src="{{ $friendFighter?->portrait_path ?? ($friendFighter?->source_photo_path ?? asset('images/naruto-hero.png')) }}" 
                                     alt="{{ $friendUser->username }}" 
                                     class="w-full h-full object-cover">
                                <span class="absolute bottom-0 right-0 w-3.5 h-3.5 rounded-full {{ $isOnline ? 'bg-emerald-400 ring-2 ring-shinobi-dark' : 'bg-slate-600 ring-2 ring-shinobi-dark' }}" title="{{ $isOnline ? 'En ligne' : 'Hors ligne' }}"></span>
                            </div>

                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-serif font-black text-lg text-white group-hover:text-shinobi-gold transition truncate">
                                        {{ $friendFighter?->name ?? $friendUser->username }}
                                    </h3>
                                    <span class="rounded px-1.5 py-0.2 font-mono text-[9px] font-bold border {{ $isOnline ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' : 'bg-white/5 border-shinobi-border text-slate-500' }}">
                                        {{ $isOnline ? 'En ligne' : 'Hors ligne' }}
                                    </span>
                                </div>
                                <div class="font-mono text-xs text-slate-400 flex items-center gap-2 mt-0.5 flex-wrap">
                                    <span>Clan {{ $friendFighter?->clan ?? 'Shinobi' }}</span>
                                    <span>•</span>
                                    <span>Niveau {{ $friendFighter?->level ?? 1 }}</span>
                                    <span>•</span>
                                    <span>{{ $friendFighter?->getCalculatedRank() ?? ($friendFighter?->ninja_rank ?? 'Genin') }}</span>
                                    <span class="text-slate-600 text-[11px]">({{ $friendUser->username }})</span>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons d'Action (Défier en duel & Menu) -->
                        <div class="flex items-center gap-2 shrink-0">
                            <!-- Bouton Défier en Duel -->
                            <form method="POST" action="{{ route('friends.challenge', $friendUser) }}">
                                @csrf
                                <button type="submit" class="rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white px-5 py-2.5 font-mono text-xs font-bold shadow-lg shadow-shinobi-red/20 hover:scale-[1.02] active:scale-[0.98] transition flex items-center gap-1.5">
                                    <span>Défier en duel</span>
                                    <i class="bi bi-swords"></i>
                                </button>
                            </form>

                            <!-- Menu déroulant d'options (Voir profil Databook / Supprimer) -->
                            <div class="relative" x-data="{ open: false }">
                                <button type="button" @click="open = !open" class="p-2.5 rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-300 transition">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </button>
                                <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-44 rounded-xl border border-shinobi-border bg-shinobi-card p-1 shadow-2xl z-20 font-mono text-xs space-y-1">
                                    @if($friendFighter)
                                        <a href="{{ route('fighters.show', $friendFighter) }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-white/5">
                                            <i class="bi bi-person-vcard text-shinobi-gold"></i>
                                            <span>Voir profil Databook</span>
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('friends.destroy', $friendUser) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Retirer ce ninja de vos amis ?')" class="w-full text-left flex items-center gap-2 px-3 py-2 rounded-lg text-rose-400 hover:bg-rose-950/30">
                                            <i class="bi bi-trash"></i>
                                            <span>Supprimer l'ami</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="glass3d rounded-2xl border border-shinobi-border/70 p-10 text-center text-slate-400 font-mono text-xs space-y-3">
                        <i class="bi bi-people text-3xl text-slate-500"></i>
                        <p>Tu n'as encore aucun rival shinobi dans ta liste.</p>
                        <p class="text-[11px] text-slate-500">Utilise la barre de recherche en haut pour trouver et ajouter des ninjas de l'arène.</p>
                    </div>
                @endforelse

            @elseif($activeTab === 'requests')
                <!-- Demandes d'amitié reçues -->
                @forelse($pendingReceived as $req)
                    <div class="rounded-2xl border border-shinobi-border bg-shinobi-card p-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-shinobi-dark border border-shinobi-border overflow-hidden shrink-0 flex items-center justify-center font-mono font-bold text-white">
                                @if($req->user->fighter?->portrait_path)
                                    <img src="{{ $req->user->fighter->portrait_path }}" alt="{{ $req->user->username }}" class="w-full h-full object-cover">
                                @else
                                    {{ substr($req->user->username, 0, 1) }}
                                @endif
                            </div>
                            <div>
                                <span class="font-serif font-bold text-white text-base block">{{ $req->user->fighter?->name ?? $req->user->username }}</span>
                                <span class="font-mono text-[10px] text-slate-400">Demande d'amitié de {{ $req->user->username }} • {{ $req->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('friends.accept', $req) }}">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-shinobi-red text-white font-mono text-xs font-bold hover:bg-shinobi-red-hover transition shadow-sm">
                                    Accepter
                                </button>
                            </form>
                            <form method="POST" action="{{ route('friends.decline', $req) }}">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 rounded-xl border border-shinobi-border text-slate-300 font-mono text-xs hover:bg-white/5 transition">
                                    Refuser
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="glass3d rounded-2xl border border-shinobi-border/70 p-10 text-center text-slate-400 font-mono text-xs">
                        Aucune demande d'amitié en attente.
                    </div>
                @endforelse

            @else
                <!-- Défis reçus (Onglet dédié) -->
                @forelse($pendingChallenges as $ch)
                    <div class="rounded-2xl border border-shinobi-border bg-shinobi-card p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-full bg-shinobi-dark border border-shinobi-red overflow-hidden shrink-0">
                                <img src="{{ $ch->senderFighter?->portrait_path ?? ($ch->senderFighter?->source_photo_path ?? asset('images/naruto-hero.png')) }}" 
                                     alt="{{ $ch->sender->username }}" 
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-serif font-bold text-base text-white truncate">{{ $ch->senderFighter?->name ?? $ch->sender->username }}</h4>
                                    <span class="rounded bg-shinobi-red/20 text-shinobi-red border border-shinobi-red/30 px-1.5 py-0.2 font-mono text-[9px] font-bold uppercase">Défi</span>
                                </div>
                                <span class="font-mono text-[10px] text-slate-400 block">
                                    Niveau {{ $ch->senderFighter?->level ?? 1 }} • Clan {{ $ch->senderFighter?->clan ?? 'Shinobi' }} • {{ $ch->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <form method="POST" action="{{ route('friends.challenges.accept', $ch) }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-shinobi-red text-white font-mono text-xs font-bold hover:bg-shinobi-red-hover transition shadow-lg shadow-shinobi-red/25 flex items-center gap-1.5">
                                    <span>Accepter le duel</span>
                                    <i class="bi bi-swords"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('friends.challenges.decline', $ch) }}">
                                @csrf
                                <button type="submit" class="px-3.5 py-2 rounded-xl border border-shinobi-border text-slate-400 font-mono text-xs hover:text-white hover:bg-white/5 transition">
                                    Refuser
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="glass3d rounded-2xl border border-shinobi-border/70 p-10 text-center text-slate-400 font-mono text-xs space-y-2">
                        <i class="bi bi-shield-shaded text-2xl text-slate-500"></i>
                        <p>Aucun défi de duel en attente actuellement.</p>
                    </div>
                @endforelse

            @endif

        </div>

        <!-- ===================================================================== -->
        <!-- SIDEBAR DROITE : CONDITIONNELLE (AFFICHÉE SI VRAIES DONNÉES)          -->
        <!-- ===================================================================== -->
        @if($hasSidebarContent)
            <aside class="lg:col-span-4 space-y-5">
                
                <!-- Bloc Défis en Attente Réels -->
                @if($pendingChallenges->isNotEmpty())
                    <div class="glass3d rounded-2xl border border-shinobi-border/70 p-5 space-y-4">
                        <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-2">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-lightning-charge-fill text-shinobi-red"></i>
                                <span class="font-serif font-bold text-xs uppercase text-white tracking-wide">Défis en attente</span>
                            </div>
                            <span class="rounded bg-shinobi-red/20 text-shinobi-red font-mono text-[10px] font-bold px-2 py-0.5">
                                {{ $pendingChallenges->count() }} REÇU{{ $pendingChallenges->count() > 1 ? 'S' : '' }}
                            </span>
                        </div>

                        <div class="space-y-3 font-mono text-xs">
                            @foreach($pendingChallenges as $ch)
                                <div class="p-3 rounded-xl bg-white/5 border border-shinobi-border/60 space-y-2">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-shinobi-dark border border-shinobi-red overflow-hidden shrink-0">
                                            <img src="{{ $ch->senderFighter?->portrait_path ?? ($ch->senderFighter?->source_photo_path ?? asset('images/naruto-hero.png')) }}" alt="{{ $ch->sender->username }}" class="w-full h-full object-cover">
                                        </div>
                                        <div class="min-w-0">
                                            <strong class="text-white text-xs block truncate">{{ $ch->senderFighter?->name ?? $ch->sender->username }}</strong>
                                            <span class="text-[10px] text-slate-400">Niveau {{ $ch->senderFighter?->level ?? 1 }} • Clan {{ $ch->senderFighter?->clan ?? 'Shinobi' }}</span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] text-shinobi-gold block">Vous a défié en duel !</span>
                                    <div class="grid grid-cols-2 gap-2 pt-1">
                                        <form method="POST" action="{{ route('friends.challenges.accept', $ch) }}">
                                            @csrf
                                            <button type="submit" class="w-full py-1.5 rounded-lg bg-shinobi-red text-white text-[10px] font-bold hover:bg-shinobi-red-hover transition text-center shadow-sm">
                                                Accepter le duel
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('friends.challenges.decline', $ch) }}">
                                            @csrf
                                            <button type="submit" class="w-full py-1.5 rounded-lg border border-shinobi-border bg-white/5 text-slate-300 text-[10px] hover:bg-white/10 transition text-center">
                                                Refuser
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Bloc Activité Récente Réelle des Amis Confirmés -->
                @if($recentActivities->isNotEmpty())
                    <div class="glass3d rounded-2xl border border-shinobi-border/70 p-5 space-y-4">
                        <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-2">
                            <span class="font-serif font-bold text-xs uppercase text-white tracking-wide">Activité Récente des Amis</span>
                            <i class="bi bi-clock-history text-slate-400 text-xs"></i>
                        </div>

                        <div class="space-y-3 font-mono text-xs">
                            @foreach($recentActivities as $act)
                                <div class="flex items-start gap-2.5">
                                    <div class="w-7 h-7 rounded-full {{ $act['icon_bg'] }} flex items-center justify-center shrink-0 text-xs">
                                        {{ $act['icon'] }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-white font-semibold">{{ $act['fighter_name'] }}</span>
                                        <span class="text-slate-400">{{ $act['description'] }}</span>
                                        <span class="text-[9px] text-slate-500 block">{{ $act['time']->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-3 border-t border-shinobi-border/40 text-center">
                            <p class="font-serif italic text-[10px] text-slate-400 leading-tight">
                                « Un vrai ninja se mesure aux autres. »
                            </p>
                        </div>
                    </div>
                @endif

            </aside>
        @endif

    </div>

</div>
@endsection
