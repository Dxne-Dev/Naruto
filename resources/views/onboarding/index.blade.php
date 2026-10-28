@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 980px;">
    <!-- Wizard Header -->
    <div class="text-center mb-5">
        <span class="mono-eyebrow">RITUELS DE L'ACADÉMIE DES NINJAS</span>
        <h1 class="serif-heading mb-2" style="font-size: clamp(2.2rem, 4vw, 3.2rem);">Éveil & Forge du Shinobi</h1>
        <p class="text-muted font-mono small mx-auto" style="max-width: 580px;">
            Étape par étape : découvre ton clan par la Roue du Destin, ton élément de chakra et tes attributs Databook.
        </p>

        <!-- Progress Pills -->
        <div class="d-flex justify-content-center flex-wrap gap-2 mt-4 font-mono small">
            <span id="step-indicator-1" class="badge-step active">1. IDENTITÉ</span>
            <span id="step-indicator-2" class="badge-step">2. ROUE DU CLAN</span>
            <span id="step-indicator-3" class="badge-step">3. ROUE ÉLÉMENT</span>
            <span id="step-indicator-4" class="badge-step">4. STATS DATABOOK</span>
            <span id="step-indicator-5" class="badge-step">5. CARTE SHINOBI</span>
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
        <div id="step-1" class="wizard-step card-monad p-4 p-md-5">
            <div class="mb-4">
                <span class="mono-eyebrow text-danger">PHASE 01 / 05</span>
                <h3 class="serif-heading mb-1" style="font-size: 2rem;">Baptême du Shinobi</h3>
                <p class="text-muted small font-mono">Choisis le nom de ton combattant original et son village caché.</p>
            </div>

            <div class="mb-4">
                <label for="char_name" class="mono-eyebrow mb-1">NOM DE GUERRE DU SHINOBI *</label>
                <input type="text" id="char_name" class="form-control form-control-monad" placeholder="Ex: Kazuki, Ren, Hikari..." required value="Shinobi_{{ rand(100, 999) }}">
                <div class="text-muted font-mono small mt-1">Ce nom sera affiché dans les arbres de tournois et les classements.</div>
            </div>

            <div class="mb-5">
                <label class="mono-eyebrow mb-2">VILLAGE CACHÉ D'ORIGINE *</label>
                <div class="row g-2">
                    @foreach($villages as $idx => $v)
                        <div class="col-6 col-md">
                            <label class="village-selector-card p-3 text-center d-block {{ $idx === 0 ? 'selected' : '' }}" onclick="selectVillage('{{ $v }}', this)">
                                <div class="serif-heading mb-0" style="font-size: 1.1rem;">{{ $v }}</div>
                                <span class="text-muted font-mono" style="font-size: 10px;">Village Caché</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="text-end">
                <button type="button" class="btn-lake-blue px-4 py-3" onclick="goToStep(2)">
                    CONFIRMER & ROUE DU CLAN ▸
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 2 : ROUE DU CLAN                          -->
        <!-- ============================================== -->
        <div id="step-2" class="wizard-step card-monad p-4 p-md-5 d-none">
            <div class="text-center mb-4">
                <span class="mono-eyebrow text-danger">PHASE 02 / 05</span>
                <h3 class="serif-heading mb-1" style="font-size: 2rem;">La Roue du Destin : Les 8 Clans</h3>
                <p class="text-muted small font-mono">Fais tourner la grande roue pour hériter de ta lignée ninja et de ton don héréditaire.</p>
            </div>

            <!-- Canvas Wheel Container -->
            <div class="d-flex flex-column align-items-center justify-content-center my-4">
                <div class="wheel-box position-relative">
                    <div class="wheel-pointer"></div>
                    <canvas id="clanWheelCanvas" width="360" height="360"></canvas>
                </div>

                <button type="button" id="btn-spin-clan" class="btn-lake-blue px-5 py-3 mt-4" onclick="spinClanWheel()">
                    TOURNER LA ROUE DU CLAN ⟳
                </button>
            </div>

            <!-- Selected Clan Details Box -->
            <div id="clan-result-box" class="p-3 bg-white border rounded-4 text-center mt-3 d-none">
                <span class="mono-eyebrow text-danger">LIGNÉE ATTRIBUÉE</span>
                <h4 id="clan-result-title" class="serif-heading mb-1" style="font-size: 1.8rem;">Clan Uchiha</h4>
                <p id="clan-result-desc" class="text-muted font-mono small mb-3">Immunité au premier Genjutsu du match.</p>
                <button type="button" class="btn-off-black px-4 py-2" onclick="goToStep(3)">
                    CONTINUER VERS L'ÉLÉMENT ▸
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 3 : ROUE DE L'ÉLÉMENT DE CHAKRA           -->
        <!-- ============================================== -->
        <div id="step-3" class="wizard-step card-monad p-4 p-md-5 d-none">
            <div class="text-center mb-4">
                <span class="mono-eyebrow text-danger">PHASE 03 / 05</span>
                <h3 class="serif-heading mb-1" style="font-size: 2rem;">La Roue des 5 Affinités Élémentaires</h3>
                <p class="text-muted small font-mono">Katon, Fūton, Raiton, Doton, Suiton : découvre la nature de ton chakra.</p>
            </div>

            <div class="d-flex flex-column align-items-center justify-content-center my-4">
                <div class="wheel-box position-relative">
                    <div class="wheel-pointer"></div>
                    <canvas id="elementWheelCanvas" width="360" height="360"></canvas>
                </div>

                <button type="button" id="btn-spin-element" class="btn-lake-blue px-5 py-3 mt-4" onclick="spinElementWheel()">
                    TOURNER LA ROUE DE CHAKRA ⟳
                </button>
            </div>

            <div id="element-result-box" class="p-3 bg-white border rounded-4 text-center mt-3 d-none">
                <span class="mono-eyebrow text-danger">AFFINITÉ ÉVEILLÉE</span>
                <h4 id="element-result-title" class="serif-heading mb-1" style="font-size: 1.8rem;">Nature Katon</h4>
                <p id="element-result-desc" class="text-muted font-mono small mb-3">Multiplicateur élémentaire ×1.25 face au Fūton.</p>
                <button type="button" class="btn-off-black px-4 py-2" onclick="goToStep(4)">
                    CONTINUER VERS LES STATS DATABOOK ▸
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 4 : STATISTIQUES DU DATABOOK              -->
        <!-- ============================================== -->
        <div id="step-4" class="wizard-step card-monad p-4 p-md-5 d-none">
            <div class="text-center mb-4">
                <span class="mono-eyebrow text-danger">PHASE 04 / 05</span>
                <h3 class="serif-heading mb-1" style="font-size: 2rem;">Attributs Canoniques du Databook</h3>
                <p class="text-muted small font-mono">Les 8 statistiques officielles (1.0 à 5.0) façonnées selon le hasard, ton clan et ton affinité.</p>
            </div>

            <div class="row g-3 font-mono">
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-white border rounded-3 text-center stat-roll-box">
                        <span class="mono-eyebrow">NINJUTSU</span>
                        <div id="disp_stat_nin" class="fs-3 fw-bold text-dark">0.0</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-white border rounded-3 text-center stat-roll-box">
                        <span class="mono-eyebrow">TAIJUTSU</span>
                        <div id="disp_stat_tai" class="fs-3 fw-bold text-dark">0.0</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-white border rounded-3 text-center stat-roll-box">
                        <span class="mono-eyebrow">GENJUTSU</span>
                        <div id="disp_stat_gen" class="fs-3 fw-bold text-dark">0.0</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-white border rounded-3 text-center stat-roll-box">
                        <span class="mono-eyebrow">INTELLIGENCE</span>
                        <div id="disp_stat_int" class="fs-3 fw-bold text-dark">0.0</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-white border rounded-3 text-center stat-roll-box">
                        <span class="mono-eyebrow">FORCE</span>
                        <div id="disp_stat_str" class="fs-3 fw-bold text-dark">0.0</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-white border rounded-3 text-center stat-roll-box">
                        <span class="mono-eyebrow">VITESSE</span>
                        <div id="disp_stat_spd" class="fs-3 fw-bold text-dark">0.0</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-white border rounded-3 text-center stat-roll-box">
                        <span class="mono-eyebrow">ENDURANCE (PV)</span>
                        <div id="disp_stat_end" class="fs-3 fw-bold text-dark">0.0</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-white border rounded-3 text-center stat-roll-box">
                        <span class="mono-eyebrow">SCEAUX (CHAKRA)</span>
                        <div id="disp_stat_sea" class="fs-3 fw-bold text-dark">0.0</div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5">
                <button type="button" id="btn-reveal-stats" class="btn-lake-blue px-5 py-3 d-inline-flex items-center justify-content-center gap-2" onclick="revealStatsAnimation()">
                    <i class="bi bi-lightning-charge-fill"></i>
                    <span>RÉVÉLER MON POTENTIEL & CALCULER MES STATS</span>
                </button>
                <div id="stats-validated-box" class="d-none mt-3">
                    <button type="button" class="btn-lake-blue px-5 py-3 shadow d-inline-flex items-center justify-content-center gap-2" onclick="goToStep(5)">
                        <i class="bi bi-person-badge-fill"></i>
                        <span>GÉNÉRER LA CARTE DE MON SHINOBI ▸</span>
                    </button>
                    <div class="text-muted font-mono small mt-2">
                        Tous tes attributs de combat sont prêts pour l'impression du parchemin officiel.
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 5 : CARTE DU SHINOBI & MASQUE GLACÉ      -->
        <!-- ============================================== -->
        <div id="step-5" class="wizard-step card-monad p-4 p-md-5 d-none">
            
            <!-- Phase 5A : Demande de Photo -->
            <div id="card-photo-phase" class="text-center">
                <span class="mono-eyebrow text-danger">PHASE FINALE &bull; PORTRAIT OFFICIEL</span>
                <h3 class="serif-heading mb-2" style="font-size: 2.2rem;">Importe la photo de ton Shinobi</h3>
                <p class="text-muted small font-mono mx-auto mb-4" style="max-width: 540px;">
                    Téléverse une photo ou choisis un visage. Notre rouleau ninja façonne instantanément ta carte de combattant officielle.
                </p>

                <div class="p-4 p-md-5 bg-white border border-2 border-dashed rounded-4 mx-auto mb-4" style="max-width: 520px; border-color: var(--color-ash) !important;">
                    <div class="mb-3">
                        <i class="bi bi-camera text-danger fs-1"></i>
                    </div>
                    <label for="shinobi-photo-upload" class="btn-lake-blue px-4 py-2 mb-2 cursor-pointer font-mono small">
                        <i class="bi bi-upload me-1"></i> SÉLECTIONNER UNE PHOTO
                    </label>
                    <input type="file" id="shinobi-photo-upload" name="source_photo_file" accept="image/*" class="d-none" onchange="handlePhotoUpload(this)">
                    <div class="text-muted font-mono" style="font-size: 11px;">
                        JPG, PNG ou WebP (max 5 Mo)
                    </div>

                    <div class="d-flex align-items-center gap-2 my-3">
                        <hr class="flex-grow-1 text-muted">
                        <span class="font-mono text-muted small" style="font-size: 10px;">OU UTILISER UN AVATAR PRÉDÉFINI</span>
                        <hr class="flex-grow-1 text-muted">
                    </div>

                    <!-- Presets Avatars -->
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button type="button" class="btn-ghost-monad py-1 px-3 font-mono" style="font-size: 11px;" onclick="usePresetPhoto('https://images.unsplash.com/photo-1578632767115-351597cf2477?w=400&auto=format&fit=crop&q=80')">
                            Shinobi Sombre
                        </button>
                        <button type="button" class="btn-ghost-monad py-1 px-3 font-mono" style="font-size: 11px;" onclick="usePresetPhoto('https://images.unsplash.com/photo-1534447677768-be436bb09401?w=400&auto=format&fit=crop&q=80')">
                            Kunoichi Éclair
                        </button>
                        <button type="button" class="btn-ghost-monad py-1 px-3 font-mono" style="font-size: 11px;" onclick="usePresetPhoto('https://images.unsplash.com/photo-1563089145-599997674d42?w=400&auto=format&fit=crop&q=80')">
                            Guerrier Flamme
                        </button>
                    </div>
                </div>

                <div class="text-center">
                    <button type="button" class="btn-link text-muted font-mono small text-decoration-none" onclick="finalizeCreation(false)">
                        Ignorer cette étape et continuer sans carte personnalisée ▸
                    </button>
                </div>
            </div>

            <!-- Phase 5B : Animation de Forge -->
            <div id="card-generating-phase" class="d-none text-center py-5">
                <div class="spinner-border text-danger mb-3" style="width: 3.5rem; height: 3.5rem;" role="status">
                    <span class="visually-hidden">Canalisation du chakra...</span>
                </div>
                <h4 class="serif-heading mb-1" style="font-size: 1.8rem;">Canalisation du Chakra & Forge de la Carte...</h4>
                <p class="text-muted font-mono small">Impression des sceaux élémentaires et gravure du parchemin d'arène.</p>
            </div>

            <!-- Phase 5C : La Carte Générée avec Masque Frosted Glass -->
            <div id="card-preview-phase" class="d-none">
                <div class="text-center mb-4">
                    <span class="mono-eyebrow text-danger">PARCHEMIN DU SHINOBI &bull; ÉDITION LIMITÉE</span>
                    <h3 class="serif-heading mb-1" style="font-size: 2.2rem;">Ta Carte Ninja a été Forgée !</h3>
                    <p class="text-muted small font-mono mx-auto" style="max-width: 580px;">
                        Voici l'aperçu de ton identité officielle d'arène. Déverrouille la carte pour lever le sceau et obtenir ton Pass Illimité.
                    </p>
                </div>

                <!-- Conteneur Carte avec Masque Glacé -->
                <div class="position-relative mx-auto my-4 card-frosted-container" style="max-width: 480px;">
                    <!-- Vraie Carte Ninja en fond -->
                    <div class="p-4 p-md-4 bg-white border border-2 border-dark rounded-4 shadow-lg position-relative overflow-hidden card-inner-preview">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    <span class="tag-monad tag-monad-red" id="card-badge-clan">CLAN UCHIHA</span>
                                    <span class="tag-monad" id="card-badge-village">KONOHA</span>
                                </div>
                                <h3 class="serif-heading mb-0" id="card-display-name" style="font-size: 1.8rem;">Shinobi</h3>
                                <div class="text-muted small font-mono" id="card-display-sub">
                                    RANG : Genin &bull; AFFINITÉ : Katon
                                </div>
                            </div>
                            <div class="p-2 px-3 text-center bg-white border rounded-3 font-mono">
                                <span class="fs-5 fw-bold text-danger">NIV.1</span>
                            </div>
                        </div>

                        <!-- Portrait preview -->
                        <div class="text-center mb-3">
                            <img id="card-portrait-img" src="https://images.unsplash.com/photo-1578632767115-351597cf2477?w=400&auto=format&fit=crop&q=80" alt="Shinobi Portrait" class="rounded-3 shadow-sm border" style="width: 100%; max-height: 220px; object-fit: cover;">
                        </div>

                        <!-- 8 Databook Stats Grid -->
                        <div class="row g-1 font-mono text-center mb-2" style="font-size: 11px;">
                            <div class="col-3 p-1 bg-light rounded border"><span class="text-muted">NIN</span> <strong id="card-stat-nin" class="d-block text-dark">3.5</strong></div>
                            <div class="col-3 p-1 bg-light rounded border"><span class="text-muted">TAI</span> <strong id="card-stat-tai" class="d-block text-dark">3.0</strong></div>
                            <div class="col-3 p-1 bg-light rounded border"><span class="text-muted">GEN</span> <strong id="card-stat-gen" class="d-block text-dark">2.5</strong></div>
                            <div class="col-3 p-1 bg-light rounded border"><span class="text-muted">INT</span> <strong id="card-stat-int" class="d-block text-dark">3.0</strong></div>
                            <div class="col-3 p-1 bg-light rounded border"><span class="text-muted">FOR</span> <strong id="card-stat-str" class="d-block text-dark">3.0</strong></div>
                            <div class="col-3 p-1 bg-light rounded border"><span class="text-muted">VIT</span> <strong id="card-stat-spd" class="d-block text-dark">3.5</strong></div>
                            <div class="col-3 p-1 bg-light rounded border"><span class="text-muted">END</span> <strong id="card-stat-end" class="d-block text-dark">3.0</strong></div>
                            <div class="col-3 p-1 bg-light rounded border"><span class="text-muted">SCE</span> <strong id="card-stat-sea" class="d-block text-dark">2.5</strong></div>
                        </div>

                        <div class="p-2 bg-light rounded border font-mono small text-center" style="font-size: 10px;" id="card-display-passive">
                            <i class="bi bi-shield-shaded text-success me-1"></i> Don Héréditaire : Sceau Ancestral
                        </div>
                    </div>

                    <!-- MASQUE FROSTED / MASQUE GLACÉ & FLOU -->
                    <div class="position-absolute top-0 start-0 w-100 h-100 rounded-4 frosted-glass-mask d-flex flex-column align-items-center justify-content-center p-4 text-center">
                        <div class="lock-shield-icon mb-2">
                            <i class="bi bi-shield-lock-fill text-danger fs-1"></i>
                        </div>
                        <span class="tag-monad tag-monad-red mb-1">CARTE SOUS SCEAU NINJA</span>
                        <h4 class="serif-heading text-dark mb-1" style="font-size: 1.5rem;">Sceau de Protection Actif</h4>
                        <p class="text-dark small font-mono mb-0 px-2" style="font-size: 11px; max-width: 320px; line-height: 1.4;">
                            Le parchemin officiel et le portrait HD sont prêts mais verrouillés.
                        </p>
                    </div>
                </div>

                <!-- Les 2 Actions Finales -->
                <div class="text-center mt-4">
                    <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center gap-3">
                        <button type="button" class="btn-lake-blue px-4 py-3 shadow font-mono text-uppercase" style="font-size: 13px; min-width: 280px;" onclick="finalizeCreation(true)">
                            <i class="bi bi-unlock-fill me-1"></i> DÉVERROUILLER MA CARTE (700 FCFA) ▸
                        </button>
                        <button type="button" class="btn-ghost-monad px-4 py-3 font-mono text-uppercase" style="font-size: 13px;" onclick="finalizeCreation(false)">
                            CONTINUER SANS CARTE ▸
                        </button>
                    </div>
                    <div class="text-muted font-mono small mt-2" style="font-size: 11px;">
                        Le déverrouillage inclut l'accès illimité à vie aux tournois et ton portrait officiel HD.
                    </div>
                </div>
            </div>

        </div>

    </form>
</div>

<style>
.badge-step {
    padding: 6px 14px;
    border-radius: 9999px;
    border: 1px solid var(--color-ash);
    background: #FFFFFF;
    color: var(--color-smoke);
}
.badge-step.active {
    background: var(--color-brand-dark);
    color: #FFFFFF;
    border-color: var(--color-brand-dark);
}
.village-selector-card {
    border: 1px solid var(--color-ash);
    border-radius: 14px;
    background: #FFFFFF;
    cursor: pointer;
    transition: all 0.2s ease;
}
.village-selector-card.selected, .village-selector-card:hover {
    border-color: var(--color-brand-red);
    box-shadow: 0 4px 12px rgba(229, 9, 20, 0.12);
}
.wheel-box {
    width: 360px;
    height: 360px;
    border-radius: 50%;
    border: 8px solid var(--color-brand-dark);
    box-shadow: 0 8px 30px rgba(0,0,0,0.25);
    background: #221F1F;
    overflow: hidden;
}
.wheel-pointer {
    position: absolute;
    top: -16px;
    left: 50%;
    transform: translateX(-50%);
    width: 0; 
    height: 0; 
    border-left: 14px solid transparent;
    border-right: 14px solid transparent;
    border-top: 28px solid var(--color-brand-red);
    z-index: 10;
}

/* Styles Masque Frosted Glass pour la carte Ninja */
.card-frosted-container {
    perspective: 1000px;
}
.card-inner-preview {
    filter: blur(1px);
    transition: filter 0.3s ease;
}
.frosted-glass-mask {
    background: rgba(255, 255, 255, 0.45);
    backdrop-filter: blur(14px) saturate(180%);
    -webkit-backdrop-filter: blur(14px) saturate(180%);
    border: 2px solid rgba(255, 255, 255, 0.7);
    box-shadow: inset 0 0 25px rgba(255, 255, 255, 0.6), 0 12px 35px rgba(0, 0, 0, 0.15);
    z-index: 5;
    transition: all 0.3s ease;
}
.cursor-pointer {
    cursor: pointer;
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

function selectVillage(v, element) {
    chosenVillage = v;
    document.getElementById('input_village').value = v;
    document.querySelectorAll('.village-selector-card').forEach(c => c.classList.remove('selected'));
    element.classList.add('selected');
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

    document.querySelectorAll('.wizard-step').forEach(s => s.classList.add('d-none'));
    document.querySelectorAll('.badge-step').forEach(b => b.classList.remove('active'));

    document.getElementById('step-' + stepNum).classList.remove('d-none');
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
    const colors = ['#221F1F', '#E50914', '#2b59d1', '#383535', '#bf0810', '#141212', '#787573', '#E50914'];

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
        ctx.stroke();

        ctx.save();
        ctx.rotate(i * sliceAngle + sliceAngle / 2);
        ctx.textAlign = 'right';
        ctx.fillStyle = '#FFFFFF';
        ctx.font = 'bold 13px monospace';
        ctx.fillText(clanNames[i], 160, 5);
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
            // Pointer is at the top center of the wheel (angle: 3*PI/2)
            // When canvas is rotated by currentAngle, slice at theta is at theta + currentAngle.
            // So theta + currentAngle = 3*PI/2 => theta = 3*PI/2 - currentAngle.
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
            document.getElementById('clan-result-box').classList.remove('d-none');
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
    const colors = ['#E50914', '#2b59d1', '#f37a0a', '#797776', '#221F1F'];

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
        ctx.stroke();

        ctx.save();
        ctx.rotate(i * sliceAngle + sliceAngle / 2);
        ctx.textAlign = 'right';
        ctx.fillStyle = '#FFFFFF';
        ctx.font = 'bold 15px monospace';
        ctx.fillText(elementsData[i], 160, 6);
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
            // Pointer at top center (3*PI/2)
            const totalSlices = elementsData.length;
            const sliceAngle = (2 * Math.PI) / totalSlices;
            const topPointerAngle = 1.5 * Math.PI;
            const normalizedAngle = (topPointerAngle - (currentAngle % (2 * Math.PI)) + 4 * Math.PI) % (2 * Math.PI);
            const winnerIdx = Math.floor(normalizedAngle / sliceAngle) % totalSlices;

            chosenElement = elementsData[winnerIdx];
            document.getElementById('input_element').value = chosenElement;
            document.getElementById('element-result-title').textContent = 'Affinité ' + chosenElement;
            document.getElementById('element-result-desc').textContent = 'Maîtrise authentique de la nature ' + chosenElement + ' éveillée.';
            document.getElementById('element-result-box').classList.remove('d-none');
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

    // Generate final randomized stats based on clan and RNG
    const finalStats = {};
    statKeys.forEach(k => {
        // Base RNG between 1.8 and 4.2
        const randomFactor = 1.8 + Math.random() * 2.2;
        const bonus = (clanBonus[k] || 0) + (k === 'stat_nin' && chosenElement === 'Katon' ? 0.3 : 0);
        const raw = randomFactor + bonus;
        // Clamp strictly between 1.0 and 5.0 with 1 decimal
        finalStats[k] = Math.min(5.0, Math.max(1.0, Math.round(raw * 10) / 10));
    });

    // Fast scrolling number slot-machine simulation
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
            // Settle on final generated stats
            statKeys.forEach(k => {
                const finalVal = finalStats[k];
                document.getElementById('input_' + k).value = finalVal;
                const dispEl = document.getElementById('disp_' + k);
                if (dispEl) {
                    dispEl.textContent = finalVal.toFixed(1);
                    dispEl.classList.add('text-danger');
                }
            });

            if (btn) btn.classList.add('d-none');
            document.getElementById('stats-validated-box')?.classList.remove('d-none');
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
            // Clear presets URL so the controller knows to prioritize file upload
            document.getElementById('input_source_photo_url').value = '';
            triggerCardForge();
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function usePresetPhoto(url) {
    chosenPhotoUrl = url;
    document.getElementById('input_source_photo_url').value = url;
    // Clear any file selection if user chooses a preset
    const fileInput = document.getElementById('shinobi-photo-upload');
    if (fileInput) fileInput.value = '';
    triggerCardForge();
}

function triggerCardForge() {
    // 1. Afficher l'animation de forge
    document.getElementById('card-photo-phase').classList.add('d-none');
    document.getElementById('card-generating-phase').classList.remove('d-none');

    // 2. Mettre à jour les données dans la carte preview
    const charName = document.getElementById('char_name').value.trim() || 'Shinobi';
    document.getElementById('card-display-name').textContent = charName;
    document.getElementById('card-badge-clan').textContent = 'CLAN ' + chosenClan.toUpperCase();
    document.getElementById('card-badge-village').textContent = chosenVillage.toUpperCase();
    document.getElementById('card-display-sub').textContent = 'RANG : Genin • AFFINITÉ : ' + chosenElement;
    document.getElementById('card-portrait-img').src = chosenPhotoUrl;

    const passiveText = clansData[chosenClan]?.passive || 'Volonté du Feu';
    document.getElementById('card-display-passive').innerHTML = '<i class="bi bi-shield-shaded text-success me-1"></i> Don Héréditaire : ' + passiveText;

    // Report des 8 stats
    const statKeys = ['nin', 'tai', 'gen', 'int', 'str', 'spd', 'end', 'sea'];
    statKeys.forEach(k => {
        const val = document.getElementById('input_stat_' + k).value;
        const el = document.getElementById('card-stat-' + k);
        if (el) el.textContent = val;
    });

    // 3. Après 1.5s de suspense magique, révéler la carte avec le masque frosted glass
    setTimeout(() => {
        document.getElementById('card-generating-phase').classList.add('d-none');
        document.getElementById('card-preview-phase').classList.remove('d-none');
    }, 1500);
}
</script>
@endpush
@endsection
