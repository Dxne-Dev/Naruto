@extends('layouts.app')

@section('title', $challengeTitle . ' — Otaku Arena')

@section('og_meta')
    <meta property="og:site_name" content="Otaku Arena">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $challengeTitle }}">
    <meta property="og:description" content="Rejoins le salon privé « {{ $tournament->title }} » (Code : {{ $tournament->room_code }}) sur Otaku Arena. Entre dans l'arène de l'Examen Chūnin et montre ta détermination !">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:url" content="{{ $shareUrl }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $challengeTitle }}">
    <meta name="twitter:description" content="Rejoins le salon privé « {{ $tournament->title }} » (Code : {{ $tournament->room_code }}) sur Otaku Arena. Révèle ton nindô !">
    <meta name="twitter:image" content="{{ $ogImage }}">
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-8 py-4 sm:py-8" x-data="{
    copied: false,
    copyUrl(url) {
        navigator.clipboard.writeText(url);
        this.copied = true;
        setTimeout(() => this.copied = false, 2500);
    }
}">

    <!-- HERO CHALLENGE BANNER -->
    <div class="relative overflow-hidden rounded-3xl border-2 border-shinobi-red/50 bg-gradient-to-b from-[#1E1118] via-shinobi-card to-shinobi-dark p-6 sm:p-10 shadow-2xl text-center space-y-6">
        
        <!-- Background decorative glows -->
        <div class="pointer-events-none absolute -top-24 left-1/2 -translate-x-1/2 h-72 w-96 rounded-full bg-shinobi-red/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-20 right-0 h-64 w-64 rounded-full bg-shinobi-gold/15 blur-3xl"></div>

        <!-- BADGE D'EN-TÊTE -->
        <div class="relative z-10 inline-flex items-center gap-2 rounded-full border border-shinobi-red/40 bg-shinobi-red/10 px-4 py-1.5 text-xs font-mono font-bold tracking-wider text-shinobi-red uppercase shadow-lg shadow-shinobi-red/20">
            <i class="bi bi-fire text-amber-400"></i>
            <span>DÉFI SHINOBI • EXAMEN CHŪNIN</span>
            <i class="bi bi-fire text-amber-400"></i>
        </div>

        <!-- AVATAR & IDENTITÉ DU DÉFIEUR -->
        <div class="relative z-10 flex flex-col items-center space-y-3">
            <div class="relative group">
                <div class="absolute -inset-1 rounded-2xl bg-gradient-to-r from-shinobi-red via-shinobi-gold to-shinobi-red opacity-75 blur group-hover:opacity-100 transition"></div>
                <div class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-2xl overflow-hidden border-2 border-white/20 bg-black shadow-2xl">
                    <img src="{{ $ogImage }}" 
                         alt="{{ $creatorShinobiName }}" 
                         class="w-full h-full object-cover object-top">
                    @if($creatorFighter)
                        <span class="absolute bottom-1 right-1 bg-shinobi-red text-white font-mono text-[10px] font-bold px-1.5 py-0.5 rounded shadow">
                            Niv. {{ $creatorFighter->level }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="space-y-1">
                <span class="text-xs font-mono uppercase tracking-widest text-slate-400">Challenger de l'Arène</span>
                <h2 class="font-serif font-black text-2xl sm:text-3xl text-white tracking-wide">
                    {{ $creatorShinobiName }}
                </h2>
                @if($creatorFighter)
                    <div class="flex items-center justify-center gap-2 font-mono text-xs text-slate-300">
                        <span class="px-2 py-0.5 rounded bg-white/5 border border-white/10 text-white font-bold">
                            {{ $creatorFighter->clan ?? 'Clan Inconnu' }}
                        </span>
                        <span>•</span>
                        <span class="text-shinobi-gold font-bold">
                            {{ $creatorFighter->primary_element ?? 'Chakra' }}
                        </span>
                        <span>•</span>
                        <span class="text-slate-400">
                            {{ $creatorFighter->village ?? 'Konoha' }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <!-- MESSAGE CENTRAL DU DÉFI -->
        <div class="relative z-10 max-w-2xl mx-auto space-y-3">
            <h1 class="font-serif font-black text-2xl sm:text-4xl text-white tracking-tight uppercase leading-snug">
                {{ $creatorShinobiName }} vous défie pour le tournoi d'examen chunin !
            </h1>
            <p class="font-sans text-sm sm:text-base text-slate-300 leading-relaxed">
                Le salon de combat privé <strong class="text-white font-semibold">« {{ $tournament->title }} »</strong> est ouvert. 
                Rejoins l'arène shinobi, affronte les meilleurs ninjas et tente de décrocher le titre d'élite !
            </p>
        </div>

        <!-- INFOS DE LA ROOM -->
        <div class="relative z-10 max-w-md mx-auto grid grid-cols-2 gap-3 font-mono text-xs">
            <div class="rounded-xl border border-shinobi-border bg-black/40 p-3 space-y-1">
                <div class="text-[10px] uppercase text-slate-400 font-bold">Code de Combat</div>
                <div class="text-lg font-black tracking-widest text-shinobi-gold select-all">
                    {{ $tournament->room_code }}
                </div>
            </div>
            <div class="rounded-xl border border-shinobi-border bg-black/40 p-3 space-y-1">
                <div class="text-[10px] uppercase text-slate-400 font-bold">Places Disponibles</div>
                <div class="text-lg font-black text-white">
                    <span class="text-shinobi-gold">{{ $tournament->entries->count() }}</span> / {{ $tournament->max_participants }}
                </div>
            </div>
        </div>

        <!-- BOUTONS D'ACTION PRINCIPAUX (CTA) -->
        <div class="relative z-10 max-w-md mx-auto space-y-3 pt-2">
            <a href="{{ route('register') }}" 
               class="w-full block py-4 px-6 rounded-2xl bg-gradient-to-r from-shinobi-red via-red-600 to-amber-600 hover:from-red-600 hover:to-amber-500 text-white font-serif font-black uppercase text-sm sm:text-base tracking-wider shadow-xl shadow-shinobi-red/30 transition transform hover:-translate-y-0.5 text-center">
                <i class="bi bi-lightning-charge-fill me-1.5"></i>
                Relever le Défi (Créer mon Shinobi)
            </a>

            <div class="flex items-center justify-center gap-2 pt-1 font-mono text-xs text-slate-400">
                <span>Tu as déjà un compte Otaku Arena ?</span>
                <a href="{{ route('login') }}" class="text-shinobi-gold hover:text-white font-bold underline transition">
                    Se Connecter
                </a>
            </div>
        </div>

    </div>

    <!-- PARTICIPANTS ACTUELS DU SALON -->
    <div class="rounded-2xl border border-shinobi-border bg-shinobi-card p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-3">
            <h3 class="font-serif font-black uppercase text-base text-white tracking-wide flex items-center gap-2">
                <i class="bi bi-people-fill text-shinobi-red"></i>
                <span>Ninjas Déjà Inscrits ({{ $tournament->entries->count() }} / {{ $tournament->max_participants }})</span>
            </h3>
            <span class="font-mono text-xs text-slate-400">
                Salon : {{ $tournament->room_code }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($tournament->entries as $entry)
                @php
                    $f = $entry->fighter;
                    $isCreator = $f?->user_id === $tournament->creator_id;
                @endphp
                <div class="rounded-xl border border-shinobi-border bg-shinobi-dark p-3 flex items-center gap-3">
                    <div class="relative w-12 h-12 rounded-lg overflow-hidden border border-shinobi-border shrink-0 bg-black">
                        <img src="{{ $f?->display_avatar_url ?? asset('images/naruto-hero.png') }}" 
                             alt="{{ $f?->name }}" 
                             class="w-full h-full object-cover object-top">
                        <span class="absolute bottom-0 right-0 bg-shinobi-red text-white font-mono text-[9px] font-bold px-1 rounded-tl">
                            N.{{ $f?->level ?? 1 }}
                        </span>
                    </div>
                    <div class="min-w-0 space-y-0.5">
                        <div class="flex items-center gap-1">
                            <h4 class="font-serif font-bold text-xs text-white truncate">{{ $f?->name }}</h4>
                            @if($isCreator)
                                <span class="text-amber-400 text-xs" title="Créateur du salon">⭐</span>
                            @endif
                        </div>
                        <div class="font-mono text-[10px] text-slate-400">
                            {{ $f?->clan ?? 'Clan' }} • <span class="text-shinobi-gold">{{ $f?->primary_element ?? 'Vent' }}</span>
                        </div>
                    </div>
                </div>
            @endforeach

            @for($i = $tournament->entries->count(); $i < $tournament->max_participants; $i++)
                <div class="rounded-xl border-2 border-dashed border-white/10 bg-black/20 p-3 flex items-center justify-center gap-2 text-slate-600 font-mono text-xs min-h-[66px]">
                    <i class="bi bi-person-plus text-base"></i>
                    <span>Place libre #{{ $i + 1 }}</span>
                </div>
            @endfor
        </div>
    </div>

    <!-- PARTAGER À D'AUTRES AMIS -->
    <div class="rounded-2xl border border-shinobi-border bg-shinobi-dark/80 p-5 flex flex-col sm:flex-row items-center justify-between gap-4 font-mono text-xs">
        <div class="text-center sm:text-left space-y-0.5">
            <span class="text-white font-bold flex items-center justify-center sm:justify-start gap-1.5">
                <i class="bi bi-share-fill text-shinobi-gold"></i>
                Tu veux convier d'autres shinobis au tournoi ?
            </span>
            <p class="text-slate-400 text-[11px]">Transmets le lien d'invitation à tes camarades de clan !</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button type="button" @click="copyUrl('{{ $shareUrl }}')"
                    class="px-4 py-2 rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 text-white font-bold transition flex items-center gap-1.5">
                <i class="bi bi-link-45deg text-base"></i>
                <span x-text="copied ? 'Lien copié !' : 'Copier le lien'"></span>
            </button>
            <a href="https://api.whatsapp.com/send?text={{ rawurlencode('⚔️ ' . $creatorShinobiName . ' vous défie pour le tournoi d\'examen chunin ! Rejoins le salon ' . $tournament->room_code . ' sur Otaku Arena : ' . $shareUrl) }}" 
               target="_blank" rel="noopener noreferrer"
               class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition flex items-center gap-1.5">
                <i class="bi bi-whatsapp text-base"></i>
                <span>WhatsApp</span>
            </a>
        </div>
    </div>

</div>
@endsection
