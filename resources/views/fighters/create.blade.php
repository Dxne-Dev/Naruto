@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1440px;">
    <div class="text-center mb-5">
        <span class="mono-eyebrow">RITUEL D'ENRÔLEMENT SHINOBI &bull; KONOHA / BORUTO</span>
        <h1 class="heading-hero mb-2">
            Invoque ton Shinobi de Légende
        </h1>
        <p class="text-muted small mx-auto font-mono" style="max-width: 680px;">
            Fais tourner les roues du destin ninja. Sélectionne ton Clan héréditaire, ton affinité de Chakra et éveille tes 8 statistiques officielles du Databook (1.0 à 5.0) avec tes techniques de combat.
        </p>
        <div class="d-inline-flex align-items-center gap-2 mt-2 px-3 py-1 bg-white" style="border: 1px solid var(--color-ash); border-radius: 9999px;">
            <span class="mono-eyebrow m-0">JETONS DE ROUES DISPONIBLES :</span>
            <span class="fw-bold font-mono" style="color: var(--color-brand-red);">{{ $wheelTokens }} JETONS</span>
        </div>
    </div>

    <div class="row justify-content-center g-4">
        <div class="col-12 col-lg-8">
            <div class="card-monad p-4 p-md-5">

                <!-- Shinobi Identity Header -->
                <div class="text-center mb-4 pb-3 border-bottom">
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-1">
                        <span class="tag-monad tag-monad-red" id="badgeClan">CLAN {{ strtoupper($initialCandidate['clan']) }}</span>
                        <span class="tag-monad" id="badgeVillage">VILLAGE : {{ strtoupper($initialCandidate['village']) }}</span>
                        <span class="tag-monad" id="badgeElement">ÉLÉMENT : {{ strtoupper($initialCandidate['primary_element']) }}</span>
                    </div>
                    <h2 class="serif-heading mb-0" id="cardName" style="font-size: 2.5rem;">{{ $initialCandidate['name_suggestion'] }}</h2>
                    <div class="text-muted small font-mono mt-1" id="cardRank">RANG : GENIN &bull; SPÉCIALISATION : <span id="cardSpec">{{ $initialCandidate['specialization'] }}</span></div>
                </div>

                <!-- Clan Passive Alert -->
                <div class="p-3 mb-4 bg-white" style="border-radius: 16px; border: 1px solid var(--color-ash); border-left: 4px solid var(--color-brand-red);">
                    <div class="d-flex align-items-center gap-2">
                        <span class="mono-eyebrow m-0" style="color: var(--color-brand-red);">DON HÉRÉDITAIRE / PASSIF DU CLAN :</span>
                    </div>
                    <div class="small text-dark font-mono mt-1" id="cardPassive">
                        {{ $initialCandidate['passive_description'] }}
                    </div>
                </div>

                <!-- 8 Databook Stats Grid (1.0 à 5.0) -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="mono-eyebrow m-0">RADAR DES 8 STATISTIQUES DU DATABOOK OFFICIEL</span>
                        <span class="tag-monad" id="totalScore">SCORE TOTAL : 18.5 / 40.0</span>
                    </div>

                    <div class="row g-3">
                        <!-- Ninjutsu -->
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-white" style="border-radius: 14px; border: 1px solid var(--color-ash);">
                                <div class="d-flex justify-content-between font-mono small">
                                    <span>Ninjutsu</span>
                                    <strong class="text-dark" id="valNin">{{ $initialCandidate['stats']['stat_nin'] }}</strong>
                                </div>
                                <div class="bar-container-monad">
                                    <div class="bar-fill-monad" id="barNin" style="width: {{ ($initialCandidate['stats']['stat_nin'] / 5.0) * 100 }}%;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Taijutsu -->
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-white" style="border-radius: 14px; border: 1px solid var(--color-ash);">
                                <div class="d-flex justify-content-between font-mono small">
                                    <span>Taijutsu</span>
                                    <strong class="text-dark" id="valTai">{{ $initialCandidate['stats']['stat_tai'] }}</strong>
                                </div>
                                <div class="bar-container-monad">
                                    <div class="bar-fill-monad" id="barTai" style="width: {{ ($initialCandidate['stats']['stat_tai'] / 5.0) * 100 }}%;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Genjutsu -->
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-white" style="border-radius: 14px; border: 1px solid var(--color-ash);">
                                <div class="d-flex justify-content-between font-mono small">
                                    <span>Genjutsu</span>
                                    <strong class="text-dark" id="valGen">{{ $initialCandidate['stats']['stat_gen'] }}</strong>
                                </div>
                                <div class="bar-container-monad">
                                    <div class="bar-fill-monad" id="barGen" style="width: {{ ($initialCandidate['stats']['stat_gen'] / 5.0) * 100 }}%;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Intelligence -->
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-white" style="border-radius: 14px; border: 1px solid var(--color-ash);">
                                <div class="d-flex justify-content-between font-mono small">
                                    <span>Intelligence</span>
                                    <strong class="text-dark" id="valInt">{{ $initialCandidate['stats']['stat_int'] }}</strong>
                                </div>
                                <div class="bar-container-monad">
                                    <div class="bar-fill-monad" id="barInt" style="width: {{ ($initialCandidate['stats']['stat_int'] / 5.0) * 100 }}%;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Force (Str) -->
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-white" style="border-radius: 14px; border: 1px solid var(--color-ash);">
                                <div class="d-flex justify-content-between font-mono small">
                                    <span>Force</span>
                                    <strong class="text-dark" id="valStr">{{ $initialCandidate['stats']['stat_str'] }}</strong>
                                </div>
                                <div class="bar-container-monad">
                                    <div class="bar-fill-monad" id="barStr" style="width: {{ ($initialCandidate['stats']['stat_str'] / 5.0) * 100 }}%;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Vitesse (Spd) -->
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-white" style="border-radius: 14px; border: 1px solid var(--color-ash);">
                                <div class="d-flex justify-content-between font-mono small">
                                    <span>Vitesse</span>
                                    <strong class="text-dark" id="valSpd">{{ $initialCandidate['stats']['stat_spd'] }}</strong>
                                </div>
                                <div class="bar-container-monad">
                                    <div class="bar-fill-monad" id="barSpd" style="width: {{ ($initialCandidate['stats']['stat_spd'] / 5.0) * 100 }}%;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Endurance (End -> HP) -->
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-white" style="border-radius: 14px; border: 1px solid var(--color-ash);">
                                <div class="d-flex justify-content-between font-mono small">
                                    <span>Endurance</span>
                                    <strong class="text-dark" id="valEnd">{{ $initialCandidate['stats']['stat_end'] }}</strong>
                                </div>
                                <div class="bar-container-monad">
                                    <div class="bar-fill-monad" id="barEnd" style="width: {{ ($initialCandidate['stats']['stat_end'] / 5.0) * 100 }}%;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Sceaux (Sea) -->
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-white" style="border-radius: 14px; border: 1px solid var(--color-ash);">
                                <div class="d-flex justify-content-between font-mono small">
                                    <span>Sceaux</span>
                                    <strong class="text-dark" id="valSea">{{ $initialCandidate['stats']['stat_sea'] }}</strong>
                                </div>
                                <div class="bar-container-monad">
                                    <div class="bar-fill-monad" id="barSea" style="width: {{ ($initialCandidate['stats']['stat_sea'] / 5.0) * 100 }}%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jutsus Catalogue Attached -->
                <div class="mb-4">
                    <span class="mono-eyebrow mb-2">CATALOGUE DE JUTSUS ÉVEILLÉS</span>
                    <div class="d-flex flex-column gap-2" id="jutsusContainer">
                        @foreach($initialCandidate['jutsus'] as $jutsu)
                            <div class="p-3 bg-white d-flex justify-content-between align-items-center" style="border-radius: 14px; border: 1px solid var(--color-ash);">
                                <div>
                                    <span class="tag-monad me-1" style="font-size: 10px;">{{ $jutsu['rank'] ?? 'C' }}</span>
                                    <strong class="text-dark small font-mono">{{ $jutsu['name'] }}</strong>
                                    <div class="text-muted" style="font-size: 11px;">{{ $jutsu['description'] }}</div>
                                </div>
                                <div class="text-end font-mono" style="font-size: 11px;">
                                    <div class="text-dark fw-bold">Puis. {{ $jutsu['base_power'] }}</div>
                                    <div class="text-muted">Chakra {{ $jutsu['chakra_cost'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Nested Wheels Controls -->
                <div class="p-4 mb-4 bg-white" style="border: 1px solid var(--color-ash); border-radius: 20px;">
                    <span class="mono-eyebrow mb-2">ROUES DE TIRAGE IMBRIQUÉES</span>
                    <div class="row g-2">
                        <div class="col-12 col-md-4">
                            <label class="mono-eyebrow mb-1">ROUE 1 : CLAN</label>
                            <select class="form-select form-select-monad w-100" id="filterClan">
                                <option value="">Clan aléatoire</option>
                                @foreach($clans as $cName => $cData)
                                    <option value="{{ $cName }}">{{ $cName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="mono-eyebrow mb-1">ROUE 2 : ÉLÉMENT</label>
                            <select class="form-select form-select-monad w-100" id="filterElement">
                                <option value="">Élément naturel</option>
                                @foreach($elements as $el)
                                    <option value="{{ $el }}">{{ $el }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <button type="button" class="btn-lake-blue w-100 py-2" id="btnRoll" style="font-size: 12px;">
                                RELANCER LES ROUES ⟳
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Form -->
                <form method="POST" action="{{ route('fighters.store') }}" id="formConfirm">
                    @csrf
                    <input type="hidden" name="name" id="inputName" value="{{ $initialCandidate['name_suggestion'] }}">
                    <input type="hidden" name="clan" id="inputClan" value="{{ $initialCandidate['clan'] }}">
                    <input type="hidden" name="village" id="inputVillage" value="{{ $initialCandidate['village'] }}">
                    <input type="hidden" name="primary_element" id="inputElement" value="{{ $initialCandidate['primary_element'] }}">
                    <input type="hidden" name="specialization" id="inputSpec" value="{{ $initialCandidate['specialization'] }}">

                    <input type="hidden" name="stat_nin" id="inputNin" value="{{ $initialCandidate['stats']['stat_nin'] }}">
                    <input type="hidden" name="stat_tai" id="inputTai" value="{{ $initialCandidate['stats']['stat_tai'] }}">
                    <input type="hidden" name="stat_gen" id="inputGen" value="{{ $initialCandidate['stats']['stat_gen'] }}">
                    <input type="hidden" name="stat_int" id="inputInt" value="{{ $initialCandidate['stats']['stat_int'] }}">
                    <input type="hidden" name="stat_str" id="inputStr" value="{{ $initialCandidate['stats']['stat_str'] }}">
                    <input type="hidden" name="stat_spd" id="inputSpd" value="{{ $initialCandidate['stats']['stat_spd'] }}">
                    <input type="hidden" name="stat_end" id="inputEnd" value="{{ $initialCandidate['stats']['stat_end'] }}">
                    <input type="hidden" name="stat_sea" id="inputSea" value="{{ $initialCandidate['stats']['stat_sea'] }}">

                    <div id="hiddenJutsusInputs">
                        @foreach($initialCandidate['jutsu_ids'] as $jId)
                            <input type="hidden" name="jutsu_ids[]" value="{{ $jId }}">
                        @endforeach
                    </div>

                    <div class="mb-4">
                        <label class="mono-eyebrow mb-1">NOM DU SHINOBI</label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-monad" id="customNameInput" value="{{ $initialCandidate['name_suggestion'] }}" required>
                            <button type="button" class="btn-ghost-monad ms-2" id="btnApplyName">MODIFIER</button>
                        </div>
                    </div>

                    <button type="submit" class="btn-lake-blue w-100 py-3" style="font-size: 14px;">
                        SCELLER MON SHINOBI DANS L'ARÈNE ▸
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnRoll = document.getElementById('btnRoll');
    const filterClan = document.getElementById('filterClan');
    const filterElement = document.getElementById('filterElement');
    const customNameInput = document.getElementById('customNameInput');
    const btnApplyName = document.getElementById('btnApplyName');

    const cardName = document.getElementById('cardName');
    const badgeClan = document.getElementById('badgeClan');
    const badgeVillage = document.getElementById('badgeVillage');
    const badgeElement = document.getElementById('badgeElement');
    const cardSpec = document.getElementById('cardSpec');
    const cardPassive = document.getElementById('cardPassive');
    const totalScore = document.getElementById('totalScore');
    const jutsusContainer = document.getElementById('jutsusContainer');
    const hiddenJutsusInputs = document.getElementById('hiddenJutsusInputs');

    const valNin = document.getElementById('valNin');
    const valTai = document.getElementById('valTai');
    const valGen = document.getElementById('valGen');
    const valInt = document.getElementById('valInt');
    const valStr = document.getElementById('valStr');
    const valSpd = document.getElementById('valSpd');
    const valEnd = document.getElementById('valEnd');
    const valSea = document.getElementById('valSea');

    const barNin = document.getElementById('barNin');
    const barTai = document.getElementById('barTai');
    const barGen = document.getElementById('barGen');
    const barInt = document.getElementById('barInt');
    const barStr = document.getElementById('barStr');
    const barSpd = document.getElementById('barSpd');
    const barEnd = document.getElementById('barEnd');
    const barSea = document.getElementById('barSea');

    const inputName = document.getElementById('inputName');
    const inputClan = document.getElementById('inputClan');
    const inputVillage = document.getElementById('inputVillage');
    const inputElement = document.getElementById('inputElement');
    const inputSpec = document.getElementById('inputSpec');

    const inputNin = document.getElementById('inputNin');
    const inputTai = document.getElementById('inputTai');
    const inputGen = document.getElementById('inputGen');
    const inputInt = document.getElementById('inputInt');
    const inputStr = document.getElementById('inputStr');
    const inputSpd = document.getElementById('inputSpd');
    const inputEnd = document.getElementById('inputEnd');
    const inputSea = document.getElementById('inputSea');

    function updateCandidate(candidate) {
        cardName.textContent = candidate.name_suggestion;
        customNameInput.value = candidate.name_suggestion;
        inputName.value = candidate.name_suggestion;

        badgeClan.textContent = 'CLAN ' + candidate.clan.toUpperCase();
        inputClan.value = candidate.clan;

        badgeVillage.textContent = 'VILLAGE : ' + candidate.village.toUpperCase();
        inputVillage.value = candidate.village;

        badgeElement.textContent = 'ÉLÉMENT : ' + candidate.primary_element.toUpperCase();
        inputElement.value = candidate.primary_element;

        cardSpec.textContent = candidate.specialization;
        inputSpec.value = candidate.specialization;

        cardPassive.textContent = candidate.passive_description;

        // Stats
        const s = candidate.stats;
        valNin.textContent = s.stat_nin;
        inputNin.value = s.stat_nin;
        barNin.style.width = ((s.stat_nin / 5.0) * 100) + '%';

        valTai.textContent = s.stat_tai;
        inputTai.value = s.stat_tai;
        barTai.style.width = ((s.stat_tai / 5.0) * 100) + '%';

        valGen.textContent = s.stat_gen;
        inputGen.value = s.stat_gen;
        barGen.style.width = ((s.stat_gen / 5.0) * 100) + '%';

        valInt.textContent = s.stat_int;
        inputInt.value = s.stat_int;
        barInt.style.width = ((s.stat_int / 5.0) * 100) + '%';

        valStr.textContent = s.stat_str;
        inputStr.value = s.stat_str;
        barStr.style.width = ((s.stat_str / 5.0) * 100) + '%';

        valSpd.textContent = s.stat_spd;
        inputSpd.value = s.stat_spd;
        barSpd.style.width = ((s.stat_spd / 5.0) * 100) + '%';

        valEnd.textContent = s.stat_end;
        inputEnd.value = s.stat_end;
        barEnd.style.width = ((s.stat_end / 5.0) * 100) + '%';

        valSea.textContent = s.stat_sea;
        inputSea.value = s.stat_sea;
        barSea.style.width = ((s.stat_sea / 5.0) * 100) + '%';

        const sum = (s.stat_nin + s.stat_tai + s.stat_gen + s.stat_int + s.stat_str + s.stat_spd + s.stat_end + s.stat_sea).toFixed(1);
        totalScore.textContent = `SCORE TOTAL : ${sum} / 40.0`;

        // Jutsus
        jutsusContainer.innerHTML = '';
        hiddenJutsusInputs.innerHTML = '';
        candidate.jutsus.forEach(j => {
            const div = document.createElement('div');
            div.className = 'p-3 bg-white d-flex justify-content-between align-items-center';
            div.style.borderRadius = '14px';
            div.style.border = '1px solid var(--color-ash)';
            div.innerHTML = `
                <div>
                    <span class="tag-monad me-1" style="font-size: 10px;">${j.rank || 'C'}</span>
                    <strong class="text-dark small font-mono">${j.name}</strong>
                    <div class="text-muted" style="font-size: 11px;">${j.description || ''}</div>
                </div>
                <div class="text-end font-mono" style="font-size: 11px;">
                    <div class="text-dark fw-bold">Puis. ${j.base_power}</div>
                    <div class="text-muted">Chakra ${j.chakra_cost}</div>
                </div>
            `;
            jutsusContainer.appendChild(div);

            const hid = document.createElement('input');
            hid.type = 'hidden';
            hid.name = 'jutsu_ids[]';
            hid.value = j.id;
            hiddenJutsusInputs.appendChild(hid);
        });
    }

    btnRoll.addEventListener('click', async () => {
        btnRoll.disabled = true;
        btnRoll.textContent = 'INVOCATION...';

        const clan = filterClan.value;
        const elem = filterElement.value;
        const url = `{{ route('fighters.roll') }}?clan=${encodeURIComponent(clan)}&element=${encodeURIComponent(elem)}`;

        try {
            const res = await fetch(url);
            const data = await res.json();
            if (data.success && data.candidate) {
                updateCandidate(data.candidate);
            }
        } catch (e) {
            console.error(e);
        } finally {
            btnRoll.disabled = false;
            btnRoll.textContent = 'RELANCER LES ROUES ⟳';
        }
    });

    customNameInput.addEventListener('input', () => {
        const val = customNameInput.value.trim();
        cardName.textContent = val || 'SHINOBI INCONNU';
        inputName.value = val;
    });

    btnApplyName.addEventListener('click', () => {
        const val = customNameInput.value.trim();
        if (val) {
            cardName.textContent = val;
            inputName.value = val;
        }
    });
});
</script>
@endpush
