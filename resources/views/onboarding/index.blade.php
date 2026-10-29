@extends('layouts.app')

@section('title', 'Éveil & Forge du Shinobi — Otaku Arena')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:py-8 px-4">
    <!-- Wizard Header Shonen -->
    <div class="text-center mb-8 space-y-2">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-shinobi-red/10 border border-shinobi-red/30 font-mono text-[10px] sm:text-xs font-bold tracking-widest text-shinobi-red uppercase">
            <span class="w-2 h-2 rounded-full bg-shinobi-red animate-pulse"></span>
            RITUELS DE L'ACADÉMIE DES NINJAS
        </div>
        <h1 class="font-serif font-black text-3xl sm:text-4xl md:text-5xl text-white uppercase tracking-tight">
            Éveil & Forge du Shinobi
        </h1>
        <p class="font-sans text-xs sm:text-sm text-slate-300 max-w-xl mx-auto leading-relaxed">
            Étape par étape : baptise ton ninja, découvre ton clan et ton affinité de chakra par la Roue du Destin, et éveille tes attributs Databook.
        </p>

        <!-- Progress Step Pills -->
        <div class="flex items-center justify-center flex-wrap gap-2 pt-4 font-mono text-xs">
            <span id="step-indicator-1" class="step-pill active px-3.5 py-1.5 rounded-full border border-shinobi-border text-slate-400 bg-shinobi-card transition-all">
                1. IDENTITÉ
            </span>
            <span id="step-indicator-2" class="step-pill px-3.5 py-1.5 rounded-full border border-shinobi-border text-slate-400 bg-shinobi-card transition-all">
                2. ROUE DU CLAN
            </span>
            <span id="step-indicator-3" class="step-pill px-3.5 py-1.5 rounded-full border border-shinobi-border text-slate-400 bg-shinobi-card transition-all">
                3. ROUE ÉLÉMENT
            </span>
            <span id="step-indicator-4" class="step-pill px-3.5 py-1.5 rounded-full border border-shinobi-border text-slate-400 bg-shinobi-card transition-all">
                4. STATS DATABOOK
            </span>
            <span id="step-indicator-5" class="step-pill px-3.5 py-1.5 rounded-full border border-shinobi-border text-slate-400 bg-shinobi-card transition-all">
                5. CARTE SHINOBI
            </span>
        </div>
    </div>

    <form method="POST" action="{{ route('onboarding.store') }}" id="onboarding-form" enctype="multipart/form-data">
        @csrf

        <!-- Hidden Form Fields Populated By Steps -->
        <input type="hidden" name="name" id="input_name" value="">
        <input type="hidden" name="village" id="input_village" value="Konoha">
        <input type="hidden" name="clan" id="input_clan" value="Uchiha">
        <input type="hidden" name="primary_element" id="input_element" value="Katon">
        <input type="hidden" name="specialization" id="input_specialization" value="Ninjutsu">
        <input type="hidden" name="stat_nin" id="input_stat_nin" value="3.5">
        <input type="hidden" name="stat_tai" id="input_stat_tai" value="3.0">
        <input type="hidden" name="stat_gen" id="input_stat_gen" value="2.5">
        <input type="hidden" name="stat_int" id="input_stat_int" value="3.0">
        <input type="hidden" name="stat_str" id="input_stat_str" value="3.0">
        <input type="hidden" name="stat_spd" id="input_stat_spd" value="3.5">
        <input type="hidden" name="stat_end" id="input_stat_end" value="3.0">
        <input type="hidden" name="stat_sea" id="input_stat_sea" value="2.5">
        <input type="hidden" name="source_photo_url" id="input_source_photo_url" value="https://images.unsplash.com/photo-1578632767115-351597cf2477?w=400&auto=format&fit=crop&q=80">
        <input type="hidden" name="paid_microtransaction" id="input_paid_microtransaction" value="0">

        <!-- ============================================== -->
        <!-- STEP 1 : NOM & VILLAGE                         -->
        <!-- ============================================== -->
        <div id="step-1" class="wizard-step rounded-3xl border border-shinobi-border bg-[#17151A]/95 backdrop-blur-xl p-6 sm:p-8 shadow-2xl space-y-6">
            <div class="border-b border-shinobi-border/60 pb-4">
                <span class="font-mono text-xs font-bold text-shinobi-red uppercase tracking-wider">PHASE 01 / 05</span>
                <h3 class="font-serif text-2xl sm:text-3xl font-black text-white uppercase mt-1">Baptême du Shinobi</h3>
                <p class="font-mono text-xs text-slate-400 mt-1">Choisis le nom de ton combattant original et son village caché.</p>
            </div>

            <div class="space-y-2">
                <label for="char_name" class="block font-mono text-xs font-bold uppercase tracking-wider text-slate-300">
                    NOM DE GUERRE DU SHINOBI *
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500 pointer-events-none">
                        <i class="bi bi-person-badge"></i>
                    </span>
                    <input type="text" 
                           id="char_name" 
                           class="w-full rounded-xl border border-shinobi-border bg-shinobi-dark pl-10 pr-4 py-3 font-mono text-sm text-white placeholder-slate-500 focus:border-shinobi-red focus:outline-none focus:ring-1 focus:ring-shinobi-red transition" 
                           placeholder="Ex: Kazuki, Ren, Hikari..." 
                           required 
                           value="Shinobi_{{ rand(100, 999) }}">
                </div>
                <div class="font-mono text-[11px] text-slate-500">Ce nom sera affiché dans les arbres de tournois et les classements officiels.</div>
            </div>

            <div class="space-y-3">
                <label class="block font-mono text-xs font-bold uppercase tracking-wider text-slate-300">
                    VILLAGE CACHÉ D'ORIGINE *
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                    @foreach($villages as $idx => $v)
                        <button type="button" 
                                class="village-card rounded-2xl border p-3.5 text-center transition cursor-pointer select-none {{ $idx === 0 ? 'border-shinobi-red bg-shinobi-red/10 text-white shadow-lg shadow-shinobi-red/20 ring-1 ring-shinobi-red' : 'border-shinobi-border bg-shinobi-dark/60 text-slate-300 hover:border-shinobi-border hover:bg-shinobi-dark' }}" 
                                onclick="selectVillage('{{ $v }}', this)">
                            <div class="font-serif font-bold text-base text-white">{{ $v }}</div>
                            <span class="font-mono text-[10px] text-slate-400 block mt-0.5">Village Caché</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-shinobi-border/60">
                <button type="button" 
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white font-mono text-xs sm:text-sm font-bold uppercase tracking-wider shadow-lg shadow-shinobi-red/25 transition transform hover:scale-[1.02] active:scale-[0.98]" 
                        onclick="goToStep(2)">
                    <span>CONFIRMER & ROUE DU CLAN</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 2 : ROUE DU CLAN                          -->
        <!-- ============================================== -->
        <div id="step-2" class="wizard-step hidden rounded-3xl border border-shinobi-border bg-[#17151A]/95 backdrop-blur-xl p-6 sm:p-8 shadow-2xl space-y-6">
            <div class="text-center border-b border-shinobi-border/60 pb-4">
                <span class="font-mono text-xs font-bold text-shinobi-red uppercase tracking-wider">PHASE 02 / 05</span>
                <h3 class="font-serif text-2xl sm:text-3xl font-black text-white uppercase mt-1">La Roue du Destin : Les 8 Clans</h3>
                <p class="font-mono text-xs text-slate-400 mt-1">Fais tourner la grande roue pour hériter de ta lignée ninja et de ton don héréditaire.</p>
            </div>

            <!-- Canvas Wheel Container -->
            <div class="flex flex-col items-center justify-center my-6">
                <div class="relative w-[320px] h-[320px] sm:w-[360px] sm:h-[360px] rounded-full border-4 border-shinobi-border shadow-[0_0_40px_rgba(229,9,20,0.3)] bg-[#120F15] overflow-hidden flex items-center justify-center">
                    <!-- Flèche du haut -->
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-0 h-0 border-l-[12px] border-l-transparent border-r-[12px] border-r-transparent border-t-[24px] border-t-shinobi-red z-20 drop-shadow-md"></div>
                    <canvas id="clanWheelCanvas" width="360" height="360" class="w-full h-full"></canvas>
                </div>

                <button type="button" 
                        id="btn-spin-clan" 
                        class="mt-6 inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white font-mono text-xs sm:text-sm font-bold uppercase tracking-wider shadow-xl shadow-shinobi-red/30 transition transform hover:scale-105 active:scale-95" 
                        onclick="spinClanWheel()">
                    <i class="bi bi-arrow-repeat text-base"></i>
                    <span>TOURNER LA ROUE DU CLAN</span>
                </button>
            </div>

            <!-- Selected Clan Details Box -->
            <div id="clan-result-box" class="hidden p-5 rounded-2xl bg-gradient-to-r from-shinobi-red/15 to-transparent border border-shinobi-red/40 text-center space-y-2 animate-fadeIn">
                <span class="font-mono text-[10px] font-bold text-shinobi-red uppercase tracking-widest block">LIGNÉE ATTRIBUÉE</span>
                <h4 id="clan-result-title" class="font-serif text-2xl sm:text-3xl font-black text-white uppercase">Clan Uchiha</h4>
                <p id="clan-result-desc" class="font-mono text-xs text-slate-300 max-w-lg mx-auto">Immunité au premier Genjutsu du match.</p>
                <div class="pt-2">
                    <button type="button" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white text-slate-950 font-mono text-xs font-bold uppercase tracking-wider hover:bg-slate-200 transition shadow-lg" onclick="goToStep(3)">
                        <span>CONTINUER VERS L'ÉLÉMENT</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 3 : ROUE DE L'ÉLÉMENT DE CHAKRA           -->
        <!-- ============================================== -->
        <div id="step-3" class="wizard-step hidden rounded-3xl border border-shinobi-border bg-[#17151A]/95 backdrop-blur-xl p-6 sm:p-8 shadow-2xl space-y-6">
            <div class="text-center border-b border-shinobi-border/60 pb-4">
                <span class="font-mono text-xs font-bold text-shinobi-chakra uppercase tracking-wider">PHASE 03 / 05</span>
                <h3 class="font-serif text-2xl sm:text-3xl font-black text-white uppercase mt-1">Les 5 Affinités Élémentaires</h3>
                <p class="font-mono text-xs text-slate-400 mt-1">Katon, Fūton, Raiton, Doton, Suiton : découvre la nature de ton chakra.</p>
            </div>

            <div class="flex flex-col items-center justify-center my-6">
                <div class="relative w-[320px] h-[320px] sm:w-[360px] sm:h-[360px] rounded-full border-4 border-shinobi-border shadow-[0_0_40px_rgba(56,226,246,0.25)] bg-[#120F15] overflow-hidden flex items-center justify-center">
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-0 h-0 border-l-[12px] border-l-transparent border-r-[12px] border-r-transparent border-t-[24px] border-t-shinobi-chakra z-20 drop-shadow-md"></div>
                    <canvas id="elementWheelCanvas" width="360" height="360" class="w-full h-full"></canvas>
                </div>

                <button type="button" 
                        id="btn-spin-element" 
                        class="mt-6 inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-shinobi-chakra text-slate-950 hover:brightness-110 font-mono text-xs sm:text-sm font-black uppercase tracking-wider shadow-xl shadow-shinobi-chakra/25 transition transform hover:scale-105 active:scale-95" 
                        onclick="spinElementWheel()">
                    <i class="bi bi-arrow-repeat text-base"></i>
                    <span>TOURNER LA ROUE DE CHAKRA</span>
                </button>
            </div>

            <div id="element-result-box" class="hidden p-5 rounded-2xl bg-gradient-to-r from-cyan-950/40 to-transparent border border-shinobi-chakra/40 text-center space-y-2 animate-fadeIn">
                <span class="font-mono text-[10px] font-bold text-shinobi-chakra uppercase tracking-widest block">AFFINITÉ ÉVEILLÉE</span>
                <h4 id="element-result-title" class="font-serif text-2xl sm:text-3xl font-black text-white uppercase">Nature Katon</h4>
                <p id="element-result-desc" class="font-mono text-xs text-slate-300 max-w-lg mx-auto">Multiplicateur élémentaire ×1.25 face au Fūton.</p>
                <div class="pt-2">
                    <button type="button" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white text-slate-950 font-mono text-xs font-bold uppercase tracking-wider hover:bg-slate-200 transition shadow-lg" onclick="goToStep(4)">
                        <span>CONTINUER VERS LES STATS DATABOOK</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 4 : STATISTIQUES DU DATABOOK              -->
        <!-- ============================================== -->
        <div id="step-4" class="wizard-step hidden rounded-3xl border border-shinobi-border bg-[#17151A]/95 backdrop-blur-xl p-6 sm:p-8 shadow-2xl space-y-6">
            <div class="text-center border-b border-shinobi-border/60 pb-4">
                <span class="font-mono text-xs font-bold text-shinobi-gold uppercase tracking-wider">PHASE 04 / 05</span>
                <h3 class="font-serif text-2xl sm:text-3xl font-black text-white uppercase mt-1">Attributs Canoniques du Databook</h3>
                <p class="font-mono text-xs text-slate-400 mt-1">Les 8 statistiques officielles (1.0 à 5.0) façonnées selon le hasard, ton clan et ton affinité.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono">
                <div class="p-4 rounded-2xl bg-shinobi-dark/80 border border-shinobi-border text-center">
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">NINJUTSU</span>
                    <div id="disp_stat_nin" class="text-2xl sm:text-3xl font-black text-white mt-1">0.0</div>
                </div>
                <div class="p-4 rounded-2xl bg-shinobi-dark/80 border border-shinobi-border text-center">
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">TAIJUTSU</span>
                    <div id="disp_stat_tai" class="text-2xl sm:text-3xl font-black text-white mt-1">0.0</div>
                </div>
                <div class="p-4 rounded-2xl bg-shinobi-dark/80 border border-shinobi-border text-center">
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">GENJUTSU</span>
                    <div id="disp_stat_gen" class="text-2xl sm:text-3xl font-black text-white mt-1">0.0</div>
                </div>
                <div class="p-4 rounded-2xl bg-shinobi-dark/80 border border-shinobi-border text-center">
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">INTELLIGENCE</span>
                    <div id="disp_stat_int" class="text-2xl sm:text-3xl font-black text-white mt-1">0.0</div>
                </div>
                <div class="p-4 rounded-2xl bg-shinobi-dark/80 border border-shinobi-border text-center">
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">FORCE</span>
                    <div id="disp_stat_str" class="text-2xl sm:text-3xl font-black text-white mt-1">0.0</div>
                </div>
                <div class="p-4 rounded-2xl bg-shinobi-dark/80 border border-shinobi-border text-center">
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">VITESSE</span>
                    <div id="disp_stat_spd" class="text-2xl sm:text-3xl font-black text-white mt-1">0.0</div>
                </div>
                <div class="p-4 rounded-2xl bg-shinobi-dark/80 border border-shinobi-border text-center">
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">ENDURANCE (PV)</span>
                    <div id="disp_stat_end" class="text-2xl sm:text-3xl font-black text-white mt-1">0.0</div>
                </div>
                <div class="p-4 rounded-2xl bg-shinobi-dark/80 border border-shinobi-border text-center">
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">SCEAUX (CHAKRA)</span>
                    <div id="disp_stat_sea" class="text-2xl sm:text-3xl font-black text-white mt-1">0.0</div>
                </div>
            </div>

            <div class="text-center pt-4">
                <button type="button" 
                        id="btn-reveal-stats" 
                        class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-amber-500 via-shinobi-gold to-yellow-400 text-slate-950 font-mono text-xs sm:text-sm font-black uppercase tracking-wider shadow-xl shadow-shinobi-gold/20 hover:brightness-110 transition transform hover:scale-[1.02] active:scale-[0.98]" 
                        onclick="revealStatsAnimation()">
                    <i class="bi bi-lightning-charge-fill text-base"></i>
                    <span>RÉVÉLER MON POTENTIEL & CALCULER MES STATS</span>
                </button>
                <div id="stats-validated-box" class="hidden mt-4 space-y-2">
                    <button type="button" 
                            class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white font-mono text-xs sm:text-sm font-bold uppercase tracking-wider shadow-xl shadow-shinobi-red/30 transition transform hover:scale-[1.02] active:scale-[0.98]" 
                            onclick="goToStep(5)">
                        <i class="bi bi-person-badge-fill text-base"></i>
                        <span>GÉNÉRER LA CARTE DE MON SHINOBI ▸</span>
                    </button>
                    <div class="font-mono text-xs text-slate-400">
                        Tous tes attributs de combat sont prêts pour l'impression du parchemin officiel.
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 5 : CARTE DU SHINOBI & MASQUE GLACÉ      -->
        <!-- ============================================== -->
        <div id="step-5" class="wizard-step hidden rounded-3xl border border-shinobi-border bg-[#17151A]/95 backdrop-blur-xl p-6 sm:p-8 shadow-2xl space-y-6">
            
            <!-- Phase 5A : Demande de Photo -->
            <div id="card-photo-phase" class="text-center space-y-4">
                <span class="font-mono text-xs font-bold text-shinobi-red uppercase tracking-wider">PHASE FINALE &bull; PORTRAIT OFFICIEL</span>
                <h3 class="font-serif text-2xl sm:text-4xl font-black text-white uppercase">Importe la photo de ton Shinobi</h3>
                <p class="font-sans text-xs sm:text-sm text-slate-300 max-w-lg mx-auto">
                    Téléverse une photo ou choisis un visage prédéfini pour façonner ta carte de combattant officielle.
                </p>

                <div class="max-w-md mx-auto p-6 rounded-2xl border-2 border-dashed border-shinobi-border bg-shinobi-dark/60 space-y-4">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-shinobi-red/10 border border-shinobi-red/30 flex items-center justify-center text-shinobi-red text-2xl">
                        <i class="bi bi-camera"></i>
                    </div>

                    <div>
                        <label for="shinobi-photo-upload" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-shinobi-red hover:bg-shinobi-red-hover text-white font-mono text-xs font-bold uppercase tracking-wider cursor-pointer transition shadow-md">
                            <i class="bi bi-upload"></i>
                            <span>SÉLECTIONNER UNE PHOTO</span>
                        </label>
                        <input type="file" id="shinobi-photo-upload" name="source_photo_file" accept="image/*" class="hidden" onchange="handlePhotoUpload(this)">
                        <span class="block font-mono text-[10px] text-slate-500 mt-1.5">JPG, PNG ou WebP (max 5 Mo)</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="h-px bg-shinobi-border flex-1"></div>
                        <span class="font-mono text-[10px] text-slate-400 uppercase">OU UN AVATAR PRÉDÉFINI</span>
                        <div class="h-px bg-shinobi-border flex-1"></div>
                    </div>

                    <!-- Presets Avatars -->
                    <div class="flex justify-center gap-2 flex-wrap font-mono text-xs">
                        <button type="button" class="px-3 py-1.5 rounded-lg border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-200 transition" onclick="usePresetPhoto('https://images.unsplash.com/photo-1578632767115-351597cf2477?w=400&auto=format&fit=crop&q=80')">
                            Shinobi Sombre
                        </button>
                        <button type="button" class="px-3 py-1.5 rounded-lg border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-200 transition" onclick="usePresetPhoto('https://images.unsplash.com/photo-1534447677768-be436bb09401?w=400&auto=format&fit=crop&q=80')">
                            Kunoichi Éclair
                        </button>
                        <button type="button" class="px-3 py-1.5 rounded-lg border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-200 transition" onclick="usePresetPhoto('https://images.unsplash.com/photo-1563089145-599997674d42?w=400&auto=format&fit=crop&q=80')">
                            Guerrier Flamme
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="button" class="font-mono text-xs text-slate-400 hover:text-white transition" onclick="finalizeCreation(false)">
                        Ignorer cette étape et continuer sans carte personnalisée ▸
                    </button>
                </div>
            </div>

            <!-- Phase 5B : Animation de Forge -->
            <div id="card-generating-phase" class="hidden text-center py-10 space-y-3">
                <div class="w-16 h-16 mx-auto rounded-full border-4 border-shinobi-red border-t-transparent animate-spin"></div>
                <h4 class="font-serif text-2xl font-black text-white uppercase">Canalisation du Chakra & Forge de la Carte...</h4>
                <p class="font-mono text-xs text-slate-400">Impression des sceaux élémentaires et gravure du parchemin d'arène.</p>
            </div>

            <!-- Phase 5C : La Carte Générée avec Masque Frosted Glass -->
            <div id="card-preview-phase" class="hidden space-y-6">
                <div class="text-center space-y-1">
                    <span class="font-mono text-xs font-bold text-shinobi-gold uppercase tracking-wider">PARCHEMIN DU SHINOBI &bull; ÉDITION LIMITÉE</span>
                    <h3 class="font-serif text-2xl sm:text-3xl font-black text-white uppercase">Ta Carte Ninja a été Forgée !</h3>
                    <p class="font-sans text-xs sm:text-sm text-slate-300 max-w-md mx-auto">
                        Voici l'aperçu de ton identité officielle d'arène. Déverrouille la carte pour lever le sceau et obtenir ton Pass Illimité.
                    </p>
                </div>

                <!-- Conteneur Carte avec Masque Glacé -->
                <div class="relative mx-auto max-w-sm rounded-3xl border-2 border-shinobi-border bg-[#141118] p-5 shadow-2xl overflow-hidden">
                    <!-- Carte en fond -->
                    <div class="space-y-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-1.5 font-mono text-[10px] font-bold text-shinobi-red uppercase">
                                    <span id="card-badge-clan" class="px-2 py-0.5 rounded bg-shinobi-red/10 border border-shinobi-red/30">CLAN UCHIHA</span>
                                    <span id="card-badge-village" class="px-2 py-0.5 rounded bg-white/5 border border-white/10 text-slate-300">KONOHA</span>
                                </div>
                                <h3 id="card-display-name" class="font-serif text-2xl font-black text-white uppercase mt-1">Shinobi</h3>
                                <div id="card-display-sub" class="font-mono text-[11px] text-slate-400">
                                    RANG : Genin &bull; AFFINITÉ : Katon
                                </div>
                            </div>
                            <div class="px-2.5 py-1 rounded-xl bg-shinobi-red/15 border border-shinobi-red/40 text-shinobi-red font-mono text-xs font-black">
                                NIV. 1
                            </div>
                        </div>

                        <!-- Portrait preview -->
                        <div class="w-full h-48 rounded-2xl overflow-hidden border border-shinobi-border bg-black">
                            <img id="card-portrait-img" src="https://images.unsplash.com/photo-1578632767115-351597cf2477?w=400&auto=format&fit=crop&q=80" alt="Shinobi Portrait" class="w-full h-full object-cover">
                        </div>

                        <!-- 8 Databook Stats Grid -->
                        <div class="grid grid-cols-4 gap-1.5 font-mono text-center text-[10px]">
                            <div class="p-1.5 rounded-lg bg-shinobi-dark border border-shinobi-border"><span class="text-slate-400 block">NIN</span><strong id="card-stat-nin" class="text-white text-xs">3.5</strong></div>
                            <div class="p-1.5 rounded-lg bg-shinobi-dark border border-shinobi-border"><span class="text-slate-400 block">TAI</span><strong id="card-stat-tai" class="text-white text-xs">3.0</strong></div>
                            <div class="p-1.5 rounded-lg bg-shinobi-dark border border-shinobi-border"><span class="text-slate-400 block">GEN</span><strong id="card-stat-gen" class="text-white text-xs">2.5</strong></div>
                            <div class="p-1.5 rounded-lg bg-shinobi-dark border border-shinobi-border"><span class="text-slate-400 block">INT</span><strong id="card-stat-int" class="text-white text-xs">3.0</strong></div>
                            <div class="p-1.5 rounded-lg bg-shinobi-dark border border-shinobi-border"><span class="text-slate-400 block">FOR</span><strong id="card-stat-str" class="text-white text-xs">3.0</strong></div>
                            <div class="p-1.5 rounded-lg bg-shinobi-dark border border-shinobi-border"><span class="text-slate-400 block">VIT</span><strong id="card-stat-spd" class="text-white text-xs">3.5</strong></div>
                            <div class="p-1.5 rounded-lg bg-shinobi-dark border border-shinobi-border"><span class="text-slate-400 block">END</span><strong id="card-stat-end" class="text-white text-xs">3.0</strong></div>
                            <div class="p-1.5 rounded-lg bg-shinobi-dark border border-shinobi-border"><span class="text-slate-400 block">SCE</span><strong id="card-stat-sea" class="text-white text-xs">2.5</strong></div>
                        </div>

                        <div id="card-display-passive" class="p-2.5 rounded-xl bg-shinobi-dark border border-shinobi-border font-mono text-[10px] text-center text-slate-300">
                            <i class="bi bi-shield-shaded text-emerald-400 mr-1"></i> Don Héréditaire : Sceau Ancestral
                        </div>
                    </div>

                    <!-- MASQUE FROSTED / SOUS SCEAU -->
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-md flex flex-col items-center justify-center p-6 text-center space-y-2 border border-shinobi-red/50 rounded-3xl">
                        <div class="w-14 h-14 rounded-2xl bg-shinobi-red/20 border border-shinobi-red/60 flex items-center justify-center text-shinobi-red text-2xl shadow-lg shadow-shinobi-red/30">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-shinobi-red/20 border border-shinobi-red/40 text-shinobi-red font-mono text-[10px] font-bold uppercase tracking-widest">CARTE SOUS SCEAU</span>
                        <h4 class="font-serif text-xl font-bold text-white uppercase">Sceau de Protection Actif</h4>
                        <p class="font-mono text-xs text-slate-300 max-w-xs leading-relaxed">
                            Le parchemin officiel et le portrait HD sont prêts mais verrouillés.
                        </p>
                    </div>
                </div>

                <!-- Les 2 Actions Finales -->
                <div class="text-center space-y-3 pt-2">
                    <div class="flex flex-col sm:flex-row justify-center items-center gap-3">
                        <button type="button" 
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-gradient-to-r from-shinobi-gold via-amber-400 to-yellow-500 text-slate-950 font-mono text-xs font-black uppercase tracking-wider hover:brightness-110 transition shadow-xl shadow-shinobi-gold/30" 
                                onclick="finalizeCreation(true)">
                            <i class="bi bi-unlock-fill"></i>
                            <span>DÉVERROUILLER MA CARTE (700 FCFA) ▸</span>
                        </button>
                        <button type="button" 
                                class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-3.5 rounded-xl border border-shinobi-border bg-white/5 hover:bg-white/10 text-slate-300 font-mono text-xs uppercase tracking-wider transition" 
                                onclick="finalizeCreation(false)">
                            Continuer sans carte ▸
                        </button>
                    </div>
                    <div class="font-mono text-[11px] text-slate-400">
                        Le déverrouillage inclut l'accès illimité à vie aux tournois et ton portrait officiel HD.
                    </div>
                </div>
            </div>

        </div>

    </form>
</div>

<style>
.step-pill.active {
    background: #E50914 !important;
    color: #ffffff !important;
    border-color: #E50914 !important;
    box-shadow: 0 4px 14px rgba(229, 9, 20, 0.4);
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
    animation: fadeIn 0.3s ease-out forwards;
}
</style>

@push('scripts')
<script>
const clansData = @json($clans);
const elementsData = @json($elements);
const clanNames = Object.keys(clansData);

let chosenVillage = 'Konoha';
let chosenClan = 'Uchiha';
let chosenElement = 'Katon';

function selectVillage(v, btn) {
    chosenVillage = v;
    document.getElementById('input_village').value = v;
    document.querySelectorAll('.village-card').forEach(c => {
        c.className = 'village-card rounded-2xl border p-3.5 text-center transition cursor-pointer select-none border-shinobi-border bg-shinobi-dark/60 text-slate-300 hover:border-shinobi-border hover:bg-shinobi-dark';
    });
    btn.className = 'village-card rounded-2xl border p-3.5 text-center transition cursor-pointer select-none border-shinobi-red bg-shinobi-red/10 text-white shadow-lg shadow-shinobi-red/20 ring-1 ring-shinobi-red';
}

function goToStep(stepNum) {
    if (stepNum === 2) {
        const nameVal = document.getElementById('char_name').value.trim();
        if (!nameVal) {
            alert('Veuillez entrer un nom pour votre Shinobi.');
            return;
        }
        document.getElementById('input_name').value = nameVal;
    }

    document.querySelectorAll('.wizard-step').forEach(s => s.classList.add('hidden'));
    document.querySelectorAll('.step-pill').forEach(b => b.classList.remove('active'));

    document.getElementById('step-' + stepNum).classList.remove('hidden');
    document.getElementById('step-indicator-' + stepNum).classList.add('active');

    if (stepNum === 2) drawClanWheel();
    if (stepNum === 3) drawElementWheel();
}

// ----------------------------------------------------
// CANVAS ROUE 1 : CLAN WHEEL
// ----------------------------------------------------
let clanAngle = 0;
let isSpinningClan = false;

function drawClanWheel(angleOffset = 0) {
    const canvas = document.getElementById('clanWheelCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const numSlices = clanNames.length;
    const sliceAngle = (2 * Math.PI) / numSlices;
    const colors = ['#1C1618', '#E50914', '#1F2937', '#7F1D1D', '#374151', '#991B1B', '#111827', '#DC2626'];

    ctx.clearRect(0, 0, 360, 360);
    ctx.save();
    ctx.translate(180, 180);
    ctx.rotate(angleOffset);

    for (let i = 0; i < numSlices; i++) {
        ctx.beginPath();
        ctx.fillStyle = colors[i % colors.length];
        ctx.moveTo(0, 0);
        ctx.arc(0, 0, 180, i * sliceAngle, (i + 1) * sliceAngle);
        ctx.closePath();
        ctx.fill();
        ctx.strokeStyle = '#2D2933';
        ctx.lineWidth = 1.5;
        ctx.stroke();

        ctx.save();
        ctx.rotate(i * sliceAngle + sliceAngle / 2);
        ctx.textAlign = 'right';
        ctx.fillStyle = '#FFFFFF';
        ctx.font = 'bold 12px monospace';
        ctx.fillText(clanNames[i], 160, 4);
        ctx.restore();
    }
    ctx.restore();
}

function spinClanWheel() {
    if (isSpinningClan) return;
    isSpinningClan = true;
    document.getElementById('btn-spin-clan').disabled = true;

    const spins = 5 + Math.random() * 3;
    const duration = 3500;
    const start = performance.now();
    const finalOffset = Math.random() * 2 * Math.PI;

    function animate(now) {
        const elapsed = now - start;
        const progress = Math.min(elapsed / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3);
        const currentAngle = ease * (spins * 2 * Math.PI + finalOffset);

        drawClanWheel(currentAngle);

        if (progress < 1) {
            requestAnimationFrame(animate);
        } else {
            isSpinningClan = false;
            const totalSlices = clanNames.length;
            const sliceAngle = (2 * Math.PI) / totalSlices;
            const topPointerAngle = 1.5 * Math.PI;
            const normalizedAngle = (topPointerAngle - (currentAngle % (2 * Math.PI)) + 4 * Math.PI) % (2 * Math.PI);
            const winnerIdx = Math.floor(normalizedAngle / sliceAngle) % totalSlices;

            chosenClan = clanNames[winnerIdx];
            document.getElementById('input_clan').value = chosenClan;
            const clanSpec = clansData[chosenClan].favored_spec || 'Ninjutsu';
            document.getElementById('input_specialization').value = clanSpec;
            document.getElementById('clan-result-title').textContent = 'Clan ' + chosenClan + ' (' + clanSpec + ')';
            document.getElementById('clan-result-desc').textContent = clansData[chosenClan].passive;
            document.getElementById('clan-result-box').classList.remove('hidden');
        }
    }
    requestAnimationFrame(animate);
}

// ----------------------------------------------------
// CANVAS ROUE 2 : ELEMENT WHEEL
// ----------------------------------------------------
let isSpinningElement = false;

function drawElementWheel(angleOffset = 0) {
    const canvas = document.getElementById('elementWheelCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const numSlices = elementsData.length;
    const sliceAngle = (2 * Math.PI) / numSlices;
    const colors = ['#E50914', '#0284C7', '#EA580C', '#475569', '#1E293B'];

    ctx.clearRect(0, 0, 360, 360);
    ctx.save();
    ctx.translate(180, 180);
    ctx.rotate(angleOffset);

    for (let i = 0; i < numSlices; i++) {
        ctx.beginPath();
        ctx.fillStyle = colors[i % colors.length];
        ctx.moveTo(0, 0);
        ctx.arc(0, 0, 180, i * sliceAngle, (i + 1) * sliceAngle);
        ctx.closePath();
        ctx.fill();
        ctx.strokeStyle = '#2D2933';
        ctx.lineWidth = 1.5;
        ctx.stroke();

        ctx.save();
        ctx.rotate(i * sliceAngle + sliceAngle / 2);
        ctx.textAlign = 'right';
        ctx.fillStyle = '#FFFFFF';
        ctx.font = 'bold 14px monospace';
        ctx.fillText(elementsData[i], 160, 5);
        ctx.restore();
    }
    ctx.restore();
}

function spinElementWheel() {
    if (isSpinningElement) return;
    isSpinningElement = true;
    document.getElementById('btn-spin-element').disabled = true;

    const spins = 6 + Math.random() * 2;
    const duration = 3500;
    const start = performance.now();
    const finalOffset = Math.random() * 2 * Math.PI;

    function animate(now) {
        const elapsed = now - start;
        const progress = Math.min(elapsed / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3);
        const currentAngle = ease * (spins * 2 * Math.PI + finalOffset);

        drawElementWheel(currentAngle);

        if (progress < 1) {
            requestAnimationFrame(animate);
        } else {
            isSpinningElement = false;
            const totalSlices = elementsData.length;
            const sliceAngle = (2 * Math.PI) / totalSlices;
            const topPointerAngle = 1.5 * Math.PI;
            const normalizedAngle = (topPointerAngle - (currentAngle % (2 * Math.PI)) + 4 * Math.PI) % (2 * Math.PI);
            const winnerIdx = Math.floor(normalizedAngle / sliceAngle) % totalSlices;

            chosenElement = elementsData[winnerIdx];
            document.getElementById('input_element').value = chosenElement;
            document.getElementById('element-result-title').textContent = 'Affinité ' + chosenElement;
            document.getElementById('element-result-desc').textContent = 'Maîtrise authentique de la nature ' + chosenElement + ' éveillée.';
            document.getElementById('element-result-box').classList.remove('hidden');
        }
    }
    requestAnimationFrame(animate);
}

let isRevealingStats = false;

function revealStatsAnimation() {
    if (isRevealingStats) return;
    isRevealingStats = true;
    const btn = document.getElementById('btn-reveal-stats');
    if (btn) btn.disabled = true;

    const statKeys = ['stat_nin', 'stat_tai', 'stat_gen', 'stat_int', 'stat_str', 'stat_spd', 'stat_end', 'stat_sea'];
    const clanBonus = clansData[chosenClan]?.bonus_stats || {};

    const finalStats = {};
    statKeys.forEach(k => {
        const randomFactor = 1.8 + Math.random() * 2.2;
        const bonus = (clanBonus[k] || 0) + (k === 'stat_nin' && chosenElement === 'Katon' ? 0.3 : 0);
        const raw = randomFactor + bonus;
        finalStats[k] = Math.min(5.0, Math.max(1.0, Math.round(raw * 10) / 10));
    });

    let iterations = 0;
    const maxIterations = 25;
    const interval = setInterval(() => {
        iterations++;
        statKeys.forEach(k => {
            const tempVal = (1.0 + Math.random() * 4.0).toFixed(1);
            const dispEl = document.getElementById('disp_' + k);
            if (dispEl) dispEl.textContent = tempVal;
        });

        if (iterations >= maxIterations) {
            clearInterval(interval);
            statKeys.forEach(k => {
                const finalVal = finalStats[k];
                document.getElementById('input_' + k).value = finalVal;
                const dispEl = document.getElementById('disp_' + k);
                if (dispEl) {
                    dispEl.textContent = finalVal.toFixed(1);
                    dispEl.classList.add('text-shinobi-red');
                }
            });

            if (btn) btn.classList.add('hidden');
            document.getElementById('stats-validated-box')?.classList.remove('hidden');
            isRevealingStats = false;
        }
    }, 60);
}

function finalizeCreation(paid) {
    document.getElementById('input_paid_microtransaction').value = paid ? '1' : '0';
    document.getElementById('onboarding-form').submit();
}

// ----------------------------------------------------
// LOGIQUE DE LA CARTE SHINOBI (ÉTAPE 5)
// ----------------------------------------------------
let chosenPhotoUrl = 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=400&auto=format&fit=crop&q=80';

function handlePhotoUpload(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            chosenPhotoUrl = e.target.result;
            document.getElementById('input_source_photo_url').value = '';
            triggerCardForge();
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function usePresetPhoto(url) {
    chosenPhotoUrl = url;
    document.getElementById('input_source_photo_url').value = url;
    const fileInput = document.getElementById('shinobi-photo-upload');
    if (fileInput) fileInput.value = '';
    triggerCardForge();
}

function triggerCardForge() {
    document.getElementById('card-photo-phase').classList.add('hidden');
    document.getElementById('card-generating-phase').classList.remove('hidden');

    const charName = document.getElementById('char_name').value.trim() || 'Shinobi';
    document.getElementById('card-display-name').textContent = charName;
    document.getElementById('card-badge-clan').textContent = 'CLAN ' + chosenClan.toUpperCase();
    document.getElementById('card-badge-village').textContent = chosenVillage.toUpperCase();
    document.getElementById('card-display-sub').textContent = 'RANG : Genin • AFFINITÉ : ' + chosenElement;
    document.getElementById('card-portrait-img').src = chosenPhotoUrl;

    const passiveText = clansData[chosenClan]?.passive || 'Volonté du Feu';
    document.getElementById('card-display-passive').innerHTML = '<i class="bi bi-shield-shaded text-emerald-400 mr-1"></i> Don Héréditaire : ' + passiveText;

    const statKeys = ['nin', 'tai', 'gen', 'int', 'str', 'spd', 'end', 'sea'];
    statKeys.forEach(k => {
        const val = document.getElementById('input_stat_' + k).value;
        const el = document.getElementById('card-stat-' + k);
        if (el) el.textContent = val;
    });

    setTimeout(() => {
        document.getElementById('card-generating-phase').classList.add('hidden');
        document.getElementById('card-preview-phase').classList.remove('hidden');
    }, 1200);
}
</script>
@endpush
@endsection
