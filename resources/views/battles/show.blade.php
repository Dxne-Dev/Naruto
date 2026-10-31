@extends('layouts.app')

@section('title', 'Combat — ' . ($fighterA->name ?? 'Shinobi') . ' VS ' . ($fighterB->name ?? 'Rival'))

@section('content')
<div class="space-y-6 pb-16" x-data="battleReplayEngine()">

    <!-- ========================================================================= -->
    <!-- 1. EN-TÊTE DUEL & JAUGES DYNAMIQUES (TEMPS RÉEL AU FIL DE LA NARRATION)   -->
    <!-- ========================================================================= -->
    <div class="relative overflow-hidden rounded-3xl border border-shinobi-border bg-gradient-to-b from-[#1E1116] via-shinobi-card to-shinobi-dark p-6 sm:p-8 shadow-2xl">
        
        <!-- Background Glow et Particules -->
        <div class="pointer-events-none absolute -left-12 -top-12 h-64 w-64 rounded-full bg-shinobi-red/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-12 -top-12 h-64 w-64 rounded-full bg-shinobi-chakra/20 blur-3xl"></div>

        <!-- Titre Parchemin Central du Duel -->
        <div class="relative z-10 flex flex-col sm:flex-row items-center justify-between gap-3 border-b border-shinobi-border/60 pb-4 mb-6">
            <div class="rounded-xl border border-[#DFC4B5] bg-[#F5E0D7] px-4 py-1.5 text-[#181310] shadow font-serif text-sm font-bold flex items-center gap-2">
                <span class="text-shinobi-red font-black">巻</span>
                <span>DUEL D'ARÈNE — {{ $fighterA?->name }} VS {{ $fighterB?->name }}</span>
            </div>

            <!-- Compteur Dynamique de Tour -->
            <div class="flex items-center gap-3">
                <div class="rounded-full bg-white/5 border border-shinobi-border px-3 py-1 font-mono text-xs font-bold text-slate-300">
                    <span x-show="currentStepIndex === -1">Présentation</span>
                    <span x-show="currentStepIndex >= 0">
                        Tour <span class="text-shinobi-gold font-black" x-text="currentRoundNumber"></span> / {{ count($roundsTimeline) }}
                    </span>
                </div>

                <div x-show="countdown > 0" class="rounded-full bg-amber-500/20 border border-amber-500/40 px-3 py-1 font-mono text-xs font-bold text-amber-300 flex items-center gap-1.5 animate-pulse">
                    <i class="bi bi-hourglass-split"></i>
                    <span>Tour suivant dans <strong x-text="countdown"></strong>s</span>
                </div>
            </div>
        </div>

        <!-- Ligne d'affrontement (Combattant A | VS | Combattant B) -->
        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            
            <!-- COMBATTANT A (Gauche - Rouge) -->
            <div class="lg:col-span-5 rounded-2xl border p-5 space-y-4 shadow-xl transition-all duration-500"
                 :class="(currentHpA <= 0) ? 'border-rose-900 bg-black/60 opacity-60' : 'border-shinobi-border bg-shinobi-dark/80'">
                <div class="flex items-center gap-4">
                    <div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl overflow-hidden border-2 border-shinobi-red/70 shadow-lg shadow-shinobi-red/30 shrink-0 bg-black">
                        <img src="{{ $fighterA?->display_avatar_url ?? asset('images/naruto-hero.png') }}" 
                             alt="{{ $fighterA?->name }}" 
                             class="w-full h-full object-cover object-top"
                             onerror="this.onerror=null;this.src='{{ asset('images/naruto-hero.png') }}';">
                        <span class="absolute bottom-0 right-0 bg-shinobi-red text-white font-mono text-[9px] font-bold px-1.5 py-0.2 rounded-tl">
                            N.{{ $fighterA?->level ?? 1 }}
                        </span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[10px] font-black uppercase text-shinobi-red">CLAN {{ $fighterA?->clan ?? 'Shinobi' }}</span>
                            <span class="text-slate-500 font-mono text-[10px]">• {{ $fighterA?->village ?? 'Konoha' }}</span>
                        </div>
                        <h3 class="font-serif text-2xl font-black uppercase text-white truncate">{{ $fighterA?->name }}</h3>
                        <div class="font-mono text-[11px] text-slate-400">
                            Affinité : <span class="text-shinobi-red font-semibold">{{ $fighterA?->primary_element ?? 'Vent' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Jauges PV & Chakra Combattant A (ANIMÉES EN DIRECT) -->
                <div class="space-y-2.5 font-mono text-xs pt-2 border-t border-shinobi-border/60">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-slate-300 font-bold flex items-center gap-1.5"><i class="bi bi-heart-fill text-shinobi-red"></i> PV</span>
                            <strong class="text-white">
                                <span x-text="currentHpA"></span> / {{ $maxHpA }}
                            </strong>
                        </div>
                        <div class="w-full bg-black/60 rounded-full h-2.5 border border-shinobi-border/70 overflow-hidden">
                            <div class="bg-gradient-to-r from-red-700 to-shinobi-red h-full rounded-full transition-all duration-700" 
                                 :style="'width: ' + hpPercentA + '%;'"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-slate-300 font-bold flex items-center gap-1.5"><i class="bi bi-droplet-fill text-shinobi-chakra"></i> Chakra</span>
                            <strong class="text-white">
                                <span x-text="currentChakraA"></span> / {{ $maxChakraA }}
                            </strong>
                        </div>
                        <div class="w-full bg-black/60 rounded-full h-2.5 border border-shinobi-border/70 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-700 to-shinobi-chakra h-full rounded-full transition-all duration-700" 
                                 :style="'width: ' + chakraPercentA + '%;'"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SYMBOLE CENTRAL VS -->
            <div class="lg:col-span-2 text-center my-auto flex flex-col items-center justify-center space-y-2">
                <div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-br from-shinobi-red/30 via-shinobi-dark to-shinobi-chakra/30 border-2 border-shinobi-gold/60 flex items-center justify-center text-shinobi-gold font-serif text-2xl sm:text-3xl font-black shadow-2xl shadow-shinobi-gold/20 select-none animate-pulse">
                    VS
                </div>
                <div x-show="isFinished" class="rounded-full bg-shinobi-gold/20 border border-shinobi-gold/50 px-3 py-1 font-mono text-xs font-bold text-shinobi-gold uppercase shadow">
                    🏆 Vainqueur : {{ $winner?->name }}
                </div>
            </div>

            <!-- COMBATTANT B (Droite - Bleu) -->
            <div class="lg:col-span-5 rounded-2xl border p-5 space-y-4 shadow-xl transition-all duration-500"
                 :class="(currentHpB <= 0) ? 'border-rose-900 bg-black/60 opacity-60' : 'border-shinobi-border bg-shinobi-dark/80'">
                <div class="flex items-center gap-4">
                    <div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl overflow-hidden border-2 border-shinobi-chakra/70 shadow-lg shadow-shinobi-chakra/30 shrink-0 bg-black">
                        <img src="{{ $fighterB?->display_avatar_url ?? asset('images/naruto-hero.png') }}" 
                             alt="{{ $fighterB?->name }}" 
                             class="w-full h-full object-cover object-top"
                             onerror="this.onerror=null;this.src='{{ asset('images/naruto-hero.png') }}';">
                        <span class="absolute bottom-0 right-0 bg-shinobi-chakra text-shinobi-dark font-mono text-[9px] font-bold px-1.5 py-0.2 rounded-tl">
                            N.{{ $fighterB?->level ?? 1 }}
                        </span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[10px] font-black uppercase text-shinobi-chakra">CLAN {{ $fighterB?->clan ?? 'Shinobi' }}</span>
                            <span class="text-slate-500 font-mono text-[10px]">• {{ $fighterB?->village ?? 'Konoha' }}</span>
                        </div>
                        <h3 class="font-serif text-2xl font-black uppercase text-white truncate">{{ $fighterB?->name }}</h3>
                        <div class="font-mono text-[11px] text-slate-400">
                            Affinité : <span class="text-shinobi-chakra font-semibold">{{ $fighterB?->primary_element ?? 'Foudre' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Jauges PV & Chakra Combattant B (ANIMÉES EN DIRECT) -->
                <div class="space-y-2.5 font-mono text-xs pt-2 border-t border-shinobi-border/60">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-slate-300 font-bold flex items-center gap-1.5"><i class="bi bi-heart-fill text-shinobi-red"></i> PV</span>
                            <strong class="text-white">
                                <span x-text="currentHpB"></span> / {{ $maxHpB }}
                            </strong>
                        </div>
                        <div class="w-full bg-black/60 rounded-full h-2.5 border border-shinobi-border/70 overflow-hidden">
                            <div class="bg-gradient-to-r from-red-700 to-shinobi-red h-full rounded-full transition-all duration-700" 
                                 :style="'width: ' + hpPercentB + '%;'"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-slate-300 font-bold flex items-center gap-1.5"><i class="bi bi-droplet-fill text-shinobi-chakra"></i> Chakra</span>
                            <strong class="text-white">
                                <span x-text="currentChakraB"></span> / {{ $maxChakraB }}
                            </strong>
                        </div>
                        <div class="w-full bg-black/60 rounded-full h-2.5 border border-shinobi-border/70 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-700 to-shinobi-chakra h-full rounded-full transition-all duration-700" 
                                 :style="'width: ' + chakraPercentB + '%;'"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 2. PANNEAU DE LECTURE NARRATIF PROGRESSIF (MACHINE À ÉCRIRE + LOGS)       -->
    <!-- ========================================================================= -->
    @if($hasAiCommentary)
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- RÉCIT PROGRESSIF DU COMMENTATEUR IA (7 Cols) -->
        <div class="lg:col-span-7 rounded-2xl border border-shinobi-border/80 bg-shinobi-card p-5 space-y-4 shadow-xl">
            <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-shinobi-red" :class="isTyping ? 'animate-ping' : ''"></span>
                    <h3 class="font-serif font-black uppercase text-base text-white tracking-wide flex items-center gap-2">
                        <i class="bi bi-mic-fill text-shinobi-red"></i>
                        <span>Voix du Commentateur d'Arène</span>
                    </h3>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="togglePlayPause()" class="px-3 py-1.5 rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 font-mono text-xs font-bold text-white transition flex items-center gap-1.5">
                        <i :class="isPlaying ? 'bi bi-pause-fill text-amber-400' : 'bi bi-play-fill text-emerald-400'"></i>
                        <span x-text="isPlaying ? 'Pause' : 'Reprendre'"></span>
                    </button>
                    <button type="button" @click="fastForward()" class="px-3 py-1.5 rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 font-mono text-xs font-bold text-slate-300 hover:text-white transition" title="Afficher tout immédiatement">
                        <i class="bi bi-fast-forward-fill"></i>
                    </button>
                </div>
            </div>

            <!-- Conteneur Typewriter des tours déjà déroulés -->
            <div class="space-y-4 max-h-[440px] overflow-y-auto pr-2 font-sans text-xs leading-relaxed" id="narrativeScrollContainer">
                
                <!-- Introduction / Ouverture -->
                <div class="p-4 rounded-xl bg-shinobi-dark/90 border border-shinobi-border text-slate-300">
                    <div class="font-mono text-[10px] text-shinobi-gold uppercase tracking-widest font-bold mb-2 flex items-center gap-1.5">
                        <i class="bi bi-megaphone-fill"></i> OUVERTURE OFFICIELLE
                    </div>
                    <div class="prose prose-invert max-w-none text-xs text-slate-300 leading-relaxed font-sans space-y-2">
                        <div x-html="renderMarkdown(displayedIntro)"></div>
                    </div>
                    <span x-show="currentStepIndex === -1 && isTyping" class="inline-block w-1.5 h-3.5 bg-shinobi-gold ml-1 animate-pulse"></span>
                </div>

                <!-- Tours déroulés successivement -->
                <template x-for="(r, idx) in executedRounds" :key="'round-' + r.round">
                    <div class="p-4 rounded-xl border transition-all duration-300"
                         :class="(idx === executedRounds.length - 1 && isTyping) ? 'bg-gradient-to-b from-[#22171E] to-shinobi-dark border-shinobi-red/60 shadow-lg' : 'bg-shinobi-dark/80 border-shinobi-border'">
                        <div class="prose prose-invert max-w-none text-xs text-slate-200 leading-relaxed font-sans space-y-2">
                            <div x-html="renderMarkdown(r.displayedText)"></div>
                        </div>
                        <span x-show="idx === executedRounds.length - 1 && isTyping" class="inline-block w-1.5 h-3.5 bg-shinobi-red ml-1 animate-pulse"></span>
                    </div>
                </template>

                <!-- Verdict final après dernier tour -->
                <div x-show="isFinished" class="p-4 rounded-xl bg-gradient-to-r from-shinobi-gold/20 via-amber-950/30 to-shinobi-dark border-2 border-shinobi-gold text-white space-y-2">
                    <div class="font-mono text-xs font-black uppercase tracking-wider text-shinobi-gold flex items-center gap-2">
                        <i class="bi bi-trophy-fill text-base"></i>
                        <span>VERDICT FINAL DE L'ARBITRE</span>
                    </div>
                    <p class="font-sans text-xs text-slate-200">
                        Combat terminé au Round {{ count($roundsTimeline) }}. Victoire incontestable de <strong>{{ $winner?->name }}</strong> qui s'impose avec honneur dans l'arène !
                    </p>
                </div>

            </div>
        </div>

        <!-- JOURNAL TACTIQUE DES DEGATS (Droite - 5 Cols) -->
        <div class="lg:col-span-5 rounded-2xl border border-shinobi-border/80 bg-shinobi-card p-5 space-y-4 shadow-xl">
            <div class="flex items-center justify-between border-b border-shinobi-border/60 pb-3">
                <div class="flex items-center gap-2">
                    <i class="bi bi-shield-shaded text-shinobi-chakra"></i>
                    <h3 class="font-serif font-black uppercase text-base text-white tracking-wide">
                        Impacts & Dégâts Réels
                    </h3>
                </div>
                <span class="font-mono text-[10px] text-slate-400">LOGS DU COMBAT</span>
            </div>

            <!-- Liste des logs débloqués au fur et à mesure -->
            <div class="space-y-2 max-h-[440px] overflow-y-auto pr-2 font-mono text-xs">
                @forelse($battle->logs as $log)
                    @php
                        $techniqueName = $log->jutsu_used;
                        if (!$techniqueName && $log->narrative && preg_match('/\[(.*?)\]/', $log->narrative, $m)) {
                            $techniqueName = $m[1];
                        }
                    @endphp
                    <div class="p-2.5 rounded-xl border transition-all duration-300"
                         :class="(currentRoundNumber >= {{ $log->round_number }}) ? 'bg-white/5 border-shinobi-border/60' : 'bg-black/20 border-white/5 opacity-30'">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-2 min-w-0">
                                <span class="text-slate-500 font-bold text-[10px] shrink-0 mt-0.5">
                                    T.{{ $log->round_number }}
                                </span>
                                <div class="min-w-0">
                                    <span class="text-white font-semibold">{{ $log->attacker?->name ?? 'Shinobi' }}</span>
                                    <span class="text-slate-400">active</span>
                                    <span class="text-shinobi-chakra font-bold font-serif">{{ $techniqueName ?? ucfirst(str_replace('_', ' ', $log->action_type)) }}</span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                @if($log->damage_dealt > 0)
                                    <span class="rounded bg-shinobi-red/20 border border-shinobi-red/40 px-2 py-0.5 text-shinobi-red font-bold text-[10px]">
                                        -{{ $log->damage_dealt }} PV
                                    </span>
                                @else
                                    <span class="rounded bg-sky-500/10 border border-sky-500/30 px-2 py-0.5 text-sky-400 text-[10px]">
                                        Esquive
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-500">Aucun log disponible.</div>
                @endforelse
            </div>
        </div>

    </div>
    @else
    <!-- COMBAT BOT VS BOT : AFFICHAGE PLEINE LARGEUR DES LOGS TACTIQUES (SANS APPEL IA) -->
    <div class="rounded-2xl border border-shinobi-border/80 bg-shinobi-card p-6 space-y-5 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-shinobi-border/60 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-shinobi-dark border border-shinobi-border flex items-center justify-center text-shinobi-chakra text-lg shadow">
                    <i class="bi bi-cpu-fill"></i>
                </div>
                <div>
                    <h3 class="font-serif font-black uppercase text-lg text-white tracking-wide flex items-center gap-2">
                        <span>Journal Tactique du Combat</span>
                        <span class="text-[10px] font-mono font-bold uppercase px-2 py-0.5 rounded-full bg-slate-700/50 text-slate-300 border border-slate-600">
                            Simulation Bot vs Bot
                        </span>
                    </h3>
                    <p class="font-mono text-xs text-slate-400">Rapport brut du registre de combat ninja</p>
                </div>
            </div>

            <div class="rounded-xl bg-shinobi-gold/10 border border-shinobi-gold/40 px-3.5 py-1.5 font-mono text-xs text-shinobi-gold font-bold flex items-center gap-2">
                <i class="bi bi-trophy-fill"></i>
                <span>Vainqueur : <strong>{{ $winner?->name }}</strong></span>
            </div>
        </div>

        <!-- Logs du combat en grille claire et lisible -->
        <div class="space-y-3 font-mono text-xs">
            @forelse($battle->logs as $log)
                @php
                    $techniqueName = $log->jutsu_used;
                    if (!$techniqueName && $log->narrative && preg_match('/\[(.*?)\]/', $log->narrative, $m)) {
                        $techniqueName = $m[1];
                    }
                @endphp
                <div class="p-3.5 rounded-xl border border-shinobi-border/60 bg-shinobi-dark/80 hover:bg-shinobi-dark transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start sm:items-center gap-3 min-w-0">
                        <span class="rounded bg-black/60 border border-white/10 px-2 py-1 text-slate-400 font-bold text-[11px] shrink-0">
                            Tour {{ $log->round_number }}
                        </span>
                        <div class="min-w-0 space-y-0.5">
                            <div class="text-white text-sm">
                                <strong class="text-shinobi-gold">{{ $log->attacker?->name ?? 'Shinobi' }}</strong>
                                <span class="text-slate-400 text-xs">déclenche</span>
                                <span class="text-shinobi-chakra font-serif font-bold text-xs">{{ $techniqueName ?? ucfirst(str_replace('_', ' ', $log->action_type)) }}</span>
                            </div>
                            @if($log->narrative)
                                <div class="text-[11px] text-slate-400 truncate max-w-xl">{{ $log->narrative }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-3 shrink-0 self-end sm:self-auto">
                        @if($log->damage_dealt > 0)
                            <span class="rounded-lg bg-shinobi-red/20 border border-shinobi-red/40 px-3 py-1 text-shinobi-red font-bold text-xs">
                                -{{ $log->damage_dealt }} PV
                            </span>
                        @else
                            <span class="rounded-lg bg-sky-500/10 border border-sky-500/30 px-3 py-1 text-sky-400 font-bold text-xs">
                                Esquive / Bloqué
                            </span>
                        @endif

                        <span class="text-[10px] text-slate-500 hidden md:inline">
                            PV A: {{ $log->hp_remaining_a }} | PV B: {{ $log->hp_remaining_b }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-slate-500 font-sans">Aucun log enregistré pour ce duel.</div>
            @endforelse
        </div>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 3. BARRE DE COMMANDE DU REPLAY (SI NARRATION ACTIVE)                      -->
    <!-- ========================================================================= -->
    @if($hasAiCommentary)
    <div class="rounded-2xl border border-shinobi-border bg-shinobi-card p-4 flex flex-wrap items-center justify-between gap-4 font-mono text-xs">
        <div class="flex items-center gap-3">
            <span class="text-slate-400">Replay Dynamique :</span>
            <span class="text-white font-bold" x-text="isPlaying ? 'En cours de diffusion' : (isFinished ? 'Combat achevé' : 'En pause')"></span>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="restartReplay()" class="px-4 py-2 rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-300 font-bold transition flex items-center gap-1.5">
                <i class="bi bi-arrow-counterclockwise"></i>
                <span>Recommencer</span>
            </button>
            <button type="button" @click="togglePlayPause()" class="px-5 py-2 rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white font-bold shadow-lg shadow-shinobi-red/25 transition flex items-center gap-1.5">
                <i :class="isPlaying ? 'bi bi-pause-fill' : 'bi bi-play-fill'"></i>
                <span x-text="isPlaying ? 'Mettre en pause' : 'Lecture'"></span>
            </button>
            <a href="{{ route('battles.index') }}" class="px-4 py-2 rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-300 font-bold transition text-decoration-none">
                Retour aux Combats
            </a>
        </div>
    </div>
    @else
    <div class="rounded-2xl border border-shinobi-border bg-shinobi-card p-4 flex items-center justify-between font-mono text-xs">
        <span class="text-slate-400">Duel enregistré dans l'historique de l'arène</span>
        <a href="{{ route('battles.index') }}" class="px-4 py-2 rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white font-bold transition text-decoration-none flex items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>Retour aux Combats</span>
        </a>
    </div>
    @endif

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
function battleReplayEngine() {
    return {
        // Initial vital stats
        maxHpA: {{ $maxHpA }},
        maxHpB: {{ $maxHpB }},
        maxChakraA: {{ $maxChakraA }},
        maxChakraB: {{ $maxChakraB }},

        currentHpA: {{ $maxHpA }},
        currentHpB: {{ $maxHpB }},
        currentChakraA: {{ $maxChakraA }},
        currentChakraB: {{ $maxChakraB }},

        // State control
        isPlaying: true,
        isFinished: false,
        isTyping: false,
        currentStepIndex: -1, // -1: Intro, 0..N: Rounds
        countdown: 0,
        countdownTimer: null,
        typewriterTimer: null,

        // Data payload
        fullIntro: @json($introText),
        displayedIntro: '',
        allRounds: @json($roundsTimeline),
        executedRounds: [],

        get currentRoundNumber() {
            return this.currentStepIndex >= 0 ? this.currentStepIndex + 1 : 1;
        },

        get hpPercentA() {
            return Math.max(0, Math.min(100, Math.round((this.currentHpA / this.maxHpA) * 100)));
        },

        get hpPercentB() {
            return Math.max(0, Math.min(100, Math.round((this.currentHpB / this.maxHpB) * 100)));
        },

        get chakraPercentA() {
            return Math.max(0, Math.min(100, Math.round((this.currentChakraA / this.maxChakraA) * 100)));
        },

        get chakraPercentB() {
            return Math.max(0, Math.min(100, Math.round((this.currentChakraB / this.maxChakraB) * 100)));
        },

        init() {
            @if($hasAiCommentary)
            this.startReplay();
            @else
            this.isPlaying = false;
            this.isFinished = true;
            // Set final stats directly from last round
            const lastRound = this.allRounds[this.allRounds.length - 1];
            if (lastRound) {
                this.currentHpA = lastRound.hp_a;
                this.currentHpB = lastRound.hp_b;
                this.currentChakraA = lastRound.chakra_a;
                this.currentChakraB = lastRound.chakra_b;
            }
            @endif
        },

        startReplay() {
            this.resetStats();
            this.typeIntro();
        },

        resetStats() {
            this.currentHpA = this.maxHpA;
            this.currentHpB = this.maxHpB;
            this.currentChakraA = this.maxChakraA;
            this.currentChakraB = this.maxChakraB;
            this.executedRounds = [];
            this.displayedIntro = '';
            this.currentStepIndex = -1;
            this.isFinished = false;
            this.countdown = 0;
            clearInterval(this.countdownTimer);
            clearInterval(this.typewriterTimer);
        },

        restartReplay() {
            this.resetStats();
            this.isPlaying = true;
            this.typeIntro();
        },

        togglePlayPause() {
            this.isPlaying = !this.isPlaying;
        },

        typeIntro() {
            let charIndex = 0;
            const text = this.fullIntro;
            this.isTyping = true;
            this.displayedIntro = '';

            // Add by chunks of 3 characters for lively, smooth flow
            this.typewriterTimer = setInterval(() => {
                if (!this.isPlaying) return;

                if (charIndex < text.length) {
                    const nextChunk = text.slice(charIndex, charIndex + 3);
                    this.displayedIntro += nextChunk;
                    charIndex += 3;
                    this.scrollToBottom();
                } else {
                    this.displayedIntro = text;
                    clearInterval(this.typewriterTimer);
                    this.isTyping = false;
                    this.startCountdownForNextRound();
                }
            }, 18);
        },

        startCountdownForNextRound() {
            if (!this.isPlaying) return;

            this.countdown = 6; // 6 secondes de pause entre chaque tour
            this.countdownTimer = setInterval(() => {
                if (!this.isPlaying) return;

                this.countdown--;
                if (this.countdown <= 0) {
                    clearInterval(this.countdownTimer);
                    this.startNextRound();
                }
            }, 1000);
        },

        startNextRound() {
            this.currentStepIndex++;

            if (this.currentStepIndex >= this.allRounds.length) {
                this.isFinished = true;
                this.isPlaying = false;
                return;
            }

            const roundData = this.allRounds[this.currentStepIndex];
            const roundIdx = this.currentStepIndex;
            
            // Push round with empty displayedText
            this.executedRounds.push({
                round: roundData.round,
                fullText: roundData.content,
                displayedText: '',
            });

            // Animate typewriter for this round
            let charIdx = 0;
            this.isTyping = true;
            const text = roundData.content;

            this.typewriterTimer = setInterval(() => {
                if (!this.isPlaying) return;

                if (charIdx < text.length) {
                    const chunk = text.slice(charIdx, charIdx + 3);
                    this.executedRounds[roundIdx].displayedText += chunk;
                    charIdx += 3;
                    this.scrollToBottom();
                } else {
                    this.executedRounds[roundIdx].displayedText = text;
                    clearInterval(this.typewriterTimer);
                    this.isTyping = false;

                    // Update health and chakra smoothly to this round's ending stats
                    this.currentHpA = roundData.hp_a;
                    this.currentHpB = roundData.hp_b;
                    this.currentChakraA = roundData.chakra_a;
                    this.currentChakraB = roundData.chakra_b;

                    if (this.currentStepIndex < this.allRounds.length - 1) {
                        this.startCountdownForNextRound();
                    } else {
                        this.isFinished = true;
                        this.isPlaying = false;
                    }
                }
            }, 18);
        },

        fastForward() {
            clearInterval(this.typewriterTimer);
            clearInterval(this.countdownTimer);
            this.countdown = 0;
            this.displayedIntro = this.fullIntro;

            this.executedRounds = this.allRounds.map(r => ({
                round: r.round,
                fullText: r.content,
                displayedText: r.content,
            }));

            const lastRound = this.allRounds[this.allRounds.length - 1];
            if (lastRound) {
                this.currentHpA = lastRound.hp_a;
                this.currentHpB = lastRound.hp_b;
                this.currentChakraA = lastRound.chakra_a;
                this.currentChakraB = lastRound.chakra_b;
            }

            this.currentStepIndex = this.allRounds.length - 1;
            this.isFinished = true;
            this.isTyping = false;
            this.isPlaying = false;
            this.scrollToBottom();
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = document.getElementById('narrativeScrollContainer');
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        renderMarkdown(raw) {
            if (!raw) return '';

            // If marked.js is available, parse markdown cleanly
            if (typeof marked !== 'undefined' && marked.parse) {
                try {
                    let parsed = marked.parse(raw, { breaks: true, gfm: true });
                    // Custom enhancement for our status recap lines
                    parsed = parsed.replace(/📊 (.*$)/gim, '<div class="p-2.5 rounded-xl bg-shinobi-dark border border-shinobi-border font-mono text-xs text-shinobi-gold font-bold my-2 flex items-center gap-2 shadow-inner"><i class="bi bi-bar-chart-fill text-shinobi-gold"></i><span>$1</span></div>');
                    return parsed;
                } catch (e) {
                    console.error('Marked parsing error', e);
                }
            }

            // Fallback lightweight regex formatting
            let h = raw
                .replace(/^## 🔴 (.*$)/gim, '<h4 class="text-sm font-serif font-black text-shinobi-red uppercase tracking-wider my-2 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-shinobi-red"></span> $1</h4>')
                .replace(/\*\*(.*?)\*\*/g, '<strong class="text-white font-bold">$1</strong>')
                .replace(/---/g, '<hr class="border-shinobi-border/60 my-2">')
                .replace(/### (.*$)/gim, '<h3 class="text-xs font-bold text-shinobi-gold uppercase tracking-wider my-1.5">$1</h3>')
                .replace(/📊 (.*$)/gim, '<div class="p-2.5 rounded-xl bg-shinobi-dark border border-shinobi-border font-mono text-xs text-shinobi-gold font-bold my-2 flex items-center gap-2"><i class="bi bi-bar-chart-fill"></i><span>$1</span></div>')
                .replace(/\n/g, '<br>');
            return h;
        }
    };
}
</script>
@endpush
@endsection
