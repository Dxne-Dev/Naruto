@extends('layouts.app')

@section('title', 'Salons Privés — Tournois Entre Amis')

@section('content')
<div class="space-y-6">

    <!-- HERO HEADER : SALONS PRIVÉS ENTRE AMIS -->
    <div class="relative overflow-hidden rounded-3xl border border-shinobi-border bg-gradient-to-r from-shinobi-dark via-[#1A1218] to-shinobi-card p-6 sm:p-8 shadow-2xl">
        <div class="pointer-events-none absolute -right-16 -top-16 h-80 w-80 rounded-full bg-shinobi-red/15 blur-3xl"></div>
        <div class="pointer-events-none absolute left-0 top-0 h-full w-48 bg-gradient-to-r from-shinobi-red/10 to-transparent"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full border border-shinobi-red/30 bg-shinobi-red/10 px-3 py-0.5 font-mono text-[10px] font-bold tracking-wider text-shinobi-red uppercase">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>ARÈNE PRIVÉE ENTRE SHINOBIS</span>
                </div>
                <h1 class="font-serif text-3xl sm:text-4xl font-black uppercase tracking-tight text-white">
                    SALONS PRIVÉS DE TOURNOI
                </h1>
                <p class="font-mono text-xs text-slate-300 max-w-xl leading-relaxed">
                    Crée un salon sur mesure pour défier tes amis avec un code secret, ou saisis le code d'un salon existant pour rejoindre le combat !
                </p>
            </div>

            <!-- Badge Récompenses -->
            <div class="rounded-2xl border border-shinobi-border bg-black/40 p-4 space-y-2 font-mono text-xs max-w-sm shrink-0">
                <div class="text-shinobi-gold font-bold flex items-center gap-1.5 uppercase tracking-wider text-[11px]">
                    <i class="bi bi-gift-fill text-amber-400"></i>
                    <span>Récompenses du Salon</span>
                </div>
                <ul class="space-y-1.5 text-slate-300 text-[11px]">
                    <li class="flex items-center justify-between gap-3">
                        <span class="text-slate-400">Participation :</span>
                        <span class="font-bold text-white"><span class="text-emerald-400">+20 XP</span> & <span class="text-amber-400">+3 Jetons</span></span>
                    </li>
                    <li class="flex items-center justify-between gap-3">
                        <span class="text-slate-400">Champion :</span>
                        <span class="font-bold text-white"><span class="text-emerald-400">+60 XP</span> & <span class="text-amber-400">+5 Jetons</span></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    @if(!$canAccess)
    <!-- BANDEAU PASS REQUIS SI QUOTA GRATUIT ÉPUISÉ -->
    <div class="rounded-2xl border-2 border-shinobi-gold/60 bg-gradient-to-r from-amber-950/40 via-shinobi-card to-shinobi-dark p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl">
        <div class="flex items-start sm:items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-shinobi-gold/20 border border-shinobi-gold/40 flex items-center justify-center text-shinobi-gold text-lg shrink-0">
                <i class="bi bi-lock-fill"></i>
            </div>
            <div>
                <h4 class="font-serif font-black uppercase text-sm text-white">Pass Illimité Requis</h4>
                <p class="font-mono text-xs text-slate-300">
                    Tu as déjà utilisé ton tournoi privé d'essai gratuit. Débloque le Pass Illimité pour continuer à jouer avec tes amis !
                </p>
            </div>
        </div>
        <a href="{{ route('payment.checkout') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-shinobi-gold via-amber-400 to-yellow-500 text-black font-bold font-mono text-xs shadow-lg hover:scale-[1.02] transition text-center shrink-0">
            Débloquer le Pass (~1,07 €)
        </a>
    </div>
    @endif

    <!-- 2 COLONNES : CRÉER UN SALON & REJOINDRE AVEC CODE -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- COLONNE 1 : CRÉER UN SALON (7 COLS) -->
        <div class="lg:col-span-7 rounded-2xl border border-shinobi-border bg-shinobi-card p-6 space-y-5 shadow-xl">
            <div class="flex items-center gap-3 border-b border-shinobi-border/60 pb-4">
                <div class="w-9 h-9 rounded-xl bg-shinobi-red/20 border border-shinobi-red/40 flex items-center justify-center text-shinobi-red text-base">
                    <i class="bi bi-plus-circle-fill"></i>
                </div>
                <div>
                    <h3 class="font-serif font-black uppercase text-base text-white tracking-wide">
                        Créer un Salon Privé
                    </h3>
                    <p class="font-mono text-[11px] text-slate-400">Configure ton arène et invite tes amis</p>
                </div>
            </div>

            <form action="{{ route('tournaments.private.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Nom du Salon -->
                <div class="space-y-1.5">
                    <label for="title" class="font-mono text-xs font-bold text-slate-300 flex items-center gap-1.5">
                        <i class="bi bi-tag-fill text-shinobi-red"></i>
                        <span>Nom du Salon</span>
                    </label>
                    <input type="text" name="title" id="title" required minlength="3" maxlength="60"
                           value="{{ old('title', 'Examen Privé de ' . ($fighter?->name ?? auth()->user()->name)) }}"
                           placeholder="Ex: Tournoi Konoha Friends, Duel de l'Équipe 7..."
                           class="w-full rounded-xl border border-shinobi-border bg-shinobi-dark px-4 py-2.5 text-white font-mono text-xs focus:border-shinobi-red focus:outline-none transition shadow-inner">
                    @error('title')
                        <p class="font-mono text-[11px] text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nombre de participants -->
                <div class="space-y-2" x-data="{ selectedSize: '4' }">
                    <label class="font-mono text-xs font-bold text-slate-300 flex items-center gap-1.5">
                        <i class="bi bi-people-fill text-shinobi-chakra"></i>
                        <span>Nombre de Participants (Arbre Parfait)</span>
                    </label>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Option 4 Joueurs -->
                        <div @click="selectedSize = '4'" 
                             class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 select-none"
                             :class="selectedSize === '4' ? 'border-shinobi-red bg-gradient-to-b from-shinobi-red/20 to-shinobi-dark shadow-xl shadow-shinobi-red/20 scale-[1.02]' : 'border-shinobi-border/80 bg-shinobi-dark/60 hover:border-slate-500 opacity-70 hover:opacity-100'">
                            <input type="radio" name="max_participants" value="4" class="sr-only" x-model="selectedSize">
                            
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-serif font-black text-xl text-white">4 Joueurs</span>
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition"
                                     :class="selectedSize === '4' ? 'border-shinobi-red bg-shinobi-red text-white' : 'border-slate-600 bg-transparent'">
                                    <i x-show="selectedSize === '4'" class="bi bi-check text-xs font-black"></i>
                                </div>
                            </div>
                            
                            <span class="font-mono text-[11px] text-slate-300">Demi-finales + Finale</span>
                            <div class="mt-3 pt-2 border-t border-white/10 flex items-center justify-between font-mono text-[10px]">
                                <span class="text-amber-400 font-bold flex items-center gap-1"><i class="bi bi-lightning-charge-fill"></i> Rapide</span>
                                <span class="text-slate-400">3 combats</span>
                            </div>
                        </div>

                        <!-- Option 8 Joueurs -->
                        <div @click="selectedSize = '8'" 
                             class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 select-none"
                             :class="selectedSize === '8' ? 'border-shinobi-red bg-gradient-to-b from-shinobi-red/20 to-shinobi-dark shadow-xl shadow-shinobi-red/20 scale-[1.02]' : 'border-shinobi-border/80 bg-shinobi-dark/60 hover:border-slate-500 opacity-70 hover:opacity-100'">
                            <input type="radio" name="max_participants" value="8" class="sr-only" x-model="selectedSize">
                            
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-serif font-black text-xl text-white">8 Joueurs</span>
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition"
                                     :class="selectedSize === '8' ? 'border-shinobi-red bg-shinobi-red text-white' : 'border-slate-600 bg-transparent'">
                                    <i x-show="selectedSize === '8'" class="bi bi-check text-xs font-black"></i>
                                </div>
                            </div>
                            
                            <span class="font-mono text-[11px] text-slate-300">Quarts + Demies + Finale</span>
                            <div class="mt-3 pt-2 border-t border-white/10 flex items-center justify-between font-mono text-[10px]">
                                <span class="text-shinobi-chakra font-bold flex items-center gap-1"><i class="bi bi-shield-fill"></i> Grand Format</span>
                                <span class="text-slate-400">7 combats</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full py-3 rounded-xl bg-gradient-to-r from-shinobi-red to-rose-700 hover:from-shinobi-red-hover hover:to-rose-800 text-white font-serif font-black uppercase text-xs tracking-wider shadow-lg shadow-shinobi-red/30 transition flex items-center justify-center gap-2">
                        <i class="bi bi-check2-circle text-base"></i>
                        <span>Créer le Salon & Obtenir le Code</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- COLONNE 2 : REJOINDRE UN SALON VIA CODE (5 COLS) -->
        <div class="lg:col-span-5 rounded-2xl border border-shinobi-border bg-shinobi-card p-6 space-y-5 shadow-xl">
            <div class="flex items-center gap-3 border-b border-shinobi-border/60 pb-4">
                <div class="w-9 h-9 rounded-xl bg-shinobi-chakra/20 border border-shinobi-chakra/40 flex items-center justify-center text-shinobi-chakra text-base">
                    <i class="bi bi-door-open-fill"></i>
                </div>
                <div>
                    <h3 class="font-serif font-black uppercase text-base text-white tracking-wide">
                        Rejoindre un Salon
                    </h3>
                    <p class="font-mono text-[11px] text-slate-400">Un ami t'a envoyé un code ?</p>
                </div>
            </div>

            <form action="{{ route('tournaments.private.join') }}" method="POST" class="space-y-4">
                @csrf

                <div class="space-y-1.5">
                    <label for="room_code" class="font-mono text-xs font-bold text-slate-300 flex items-center gap-1.5">
                        <i class="bi bi-key-fill text-shinobi-gold"></i>
                        <span>Code du Salon Privé</span>
                    </label>
                    <input type="text" name="room_code" id="room_code" required
                           value="{{ old('room_code', request('code')) }}"
                           placeholder="Ex: SHI-742 ou KTK-891"
                           class="w-full text-center tracking-widest font-mono text-base uppercase rounded-xl border border-shinobi-border bg-shinobi-dark px-4 py-3 text-white focus:border-shinobi-chakra focus:outline-none transition shadow-inner">
                </div>

                <button type="submit" 
                        class="w-full py-3 rounded-xl bg-white/10 hover:bg-white/15 border border-shinobi-border text-white font-serif font-black uppercase text-xs tracking-wider transition flex items-center justify-center gap-2">
                    <i class="bi bi-box-arrow-in-right text-base text-shinobi-chakra"></i>
                    <span>Entrer dans le Salon</span>
                </button>
            </form>

            <div class="p-3.5 rounded-xl bg-black/40 border border-shinobi-border/60 font-mono text-[11px] text-slate-400 space-y-1">
                <div class="text-slate-300 font-bold flex items-center gap-1">
                    <i class="bi bi-info-circle-fill text-shinobi-chakra"></i>
                    <span>Comment ça marche ?</span>
                </div>
                <p>
                    Dès que le créateur ou toi rejoignez le salon, vous restez dans la salle d'attente (Lobby) jusqu'à ce que tous les combattants soient connectés.
                </p>
            </div>
        </div>

    </div>

    <!-- MES SALONS PRIVÉS EN COURS & PASSÉS -->
    <div class="rounded-2xl border border-shinobi-border bg-shinobi-card p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-3 font-mono text-xs">
            <h3 class="font-serif font-black uppercase text-base text-white tracking-wide flex items-center gap-2">
                <i class="bi bi-clock-history text-slate-400"></i>
                <span>Mes Salons Récents</span>
            </h3>
            <span class="text-slate-400">{{ $myRooms->total() }} salon(s)</span>
        </div>

        <div class="space-y-2.5 font-mono text-xs">
            @forelse($myRooms as $room)
                <div class="p-4 rounded-xl border border-shinobi-border bg-shinobi-dark/80 hover:bg-shinobi-dark transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md bg-white/10 border border-white/10 font-mono text-[10px] text-shinobi-gold font-bold">
                                {{ $room->room_code }}
                            </span>
                            <h4 class="font-serif font-bold text-sm text-white">{{ $room->title }}</h4>
                        </div>
                        <div class="font-mono text-[11px] text-slate-400 flex items-center gap-3">
                            <span>Créé par : <strong class="text-slate-300">{{ $room->creator?->name ?? 'Ninja' }}</strong></span>
                            <span>•</span>
                            <span>Participants : <strong class="text-white">{{ $room->entries_count }} / {{ $room->max_participants }}</strong></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-end sm:self-auto">
                        @if($room->status === 'completed')
                            <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-bold text-[10px]">
                                Terminé
                            </span>
                        @elseif($room->status === 'running')
                            <span class="px-2.5 py-1 rounded-lg bg-shinobi-red/10 border border-shinobi-red/30 text-shinobi-red font-bold text-[10px] animate-pulse">
                                En cours
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-300 font-bold text-[10px]">
                                En attente ({{ $room->entries_count }}/{{ $room->max_participants }})
                            </span>
                        @endif

                        <a href="{{ route('tournaments.private.lobby', $room) }}" class="px-3.5 py-1.5 rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white font-bold text-xs transition text-decoration-none">
                            Accéder au Salon
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-slate-500">
                    Tu n'as encore créé ni rejoint aucun salon privé.
                </div>
            @endforelse
        </div>

        @if($myRooms->hasPages())
            <div class="pt-2">
                {{ $myRooms->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
