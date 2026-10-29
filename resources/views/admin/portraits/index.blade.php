@extends('layouts.admin')

@section('title', 'Gestion des Portraits Shinobi - Magicien d\'Oz')

@section('content')
<div class="container-fluid content-inner pb-0">
    <!-- Header Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-kpi-custom p-4 bg-white border-0 shadow-sm">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <span class="badge bg-danger-subtle text-danger font-monospace px-3 py-1 mb-2">FORGE DES PORTRAITS &bull; MAGICIEN D'OZ</span>
                        <h2 class="fw-bold mb-1" style="letter-spacing: -0.02em;">Portraits Officiels Commandés</h2>
                        <p class="text-muted mb-0 small">
                            Tableau de commande des portraits : copie le prompt en 1 clic, génère l'illustration sur ChatGPT et téléverse le fichier pour l'injecter sur la carte du joueur.
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.portraits.index', ['status' => 'pending']) }}" class="btn {{ $currentStatus === 'pending' ? 'btn-danger' : 'btn-outline-secondary' }}">
                            <i class="bi bi-hourglass-split me-1"></i> En Attente ({{ $counts['pending'] }})
                        </a>
                        <a href="{{ route('admin.portraits.index', ['status' => 'ready']) }}" class="btn {{ $currentStatus === 'ready' ? 'btn-success' : 'btn-outline-secondary' }}">
                            <i class="bi bi-check2-circle me-1"></i> Livrés ({{ $counts['ready'] }})
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Data Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-white border-0 shadow-sm overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light font-monospace small text-uppercase">
                            <tr>
                                <th class="ps-4" style="width: 70px;">ID</th>
                                <th style="width: 110px;">Photo Source</th>
                                <th>Combattant & Joueur</th>
                                <th>Détails Shinobi</th>
                                <th style="width: 140px;">Prompt IA</th>
                                <th>Statut</th>
                                <th class="text-end pe-4" style="min-width: 240px;">Action / Téléverser</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fighters as $fighter)
                                <tr>
                                    <!-- ID -->
                                    <td class="ps-4 font-monospace text-muted small">
                                        #{{ $fighter->id }}
                                    </td>

                                    <!-- Photo Source Envoyée -->
                                    <td>
                                        @if($fighter->source_photo_path)
                                            <div class="position-relative rounded-3 overflow-hidden border shadow-sm" style="width: 72px; height: 72px; background: #0E0D0F;">
                                                <img src="{{ $fighter->source_photo_url }}" alt="Photo Source" class="w-100 h-100 object-fit-cover">
                                            </div>
                                            <a href="{{ route('admin.portraits.download-source', $fighter) }}" class="btn btn-xs btn-outline-dark mt-1 font-monospace py-0 px-1 text-nowrap" style="font-size: 10px;">
                                                <i class="bi bi-download me-0.5"></i> Télécharger
                                            </a>
                                        @else
                                            <span class="badge bg-light text-muted border font-monospace text-wrap" style="font-size: 10px; max-width: 80px;">
                                                Sans photo
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Combattant & Joueur -->
                                    <td>
                                        <div class="fw-bold fs-6 text-dark">{{ $fighter->name }}</div>
                                        <div class="text-muted small">
                                            <i class="bi bi-person me-1"></i>{{ $fighter->user?->name ?? 'Anonyme' }}
                                        </div>
                                        <div class="font-monospace text-muted" style="font-size: 11px;">
                                            {{ $fighter->user?->email }}
                                        </div>
                                    </td>

                                    <!-- Détails Shinobi (Village, Clan, Élément) -->
                                    <td>
                                        <div class="d-flex flex-wrap gap-1 mb-1">
                                            <span class="badge bg-danger-subtle text-danger font-monospace" style="font-size: 10px;">
                                                Clan {{ $fighter->clan }}
                                            </span>
                                            <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 10px;">
                                                {{ $fighter->village }}
                                            </span>
                                        </div>
                                        <div class="font-monospace text-muted small">
                                            <span>Affinité : <strong>{{ $fighter->primary_element }}</strong></span>
                                            <span class="text-dark fw-bold">&bull; {{ $fighter->specialization_label }}</span>
                                        </div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            Commandé le : {{ $fighter->portrait_ordered_at?->format('d/m/Y H:i') ?? 'N/A' }}
                                        </div>
                                    </td>

                                    <!-- Colonne Prompt : Juste le bouton Copier dans le presse-papier -->
                                    <td>
                                        {{-- Stockage du prompt dans un textarea masqué --}}
                                        <textarea id="prompt_text_{{ $fighter->id }}" class="d-none">{{ $fighter->generated_prompt }}</textarea>
                                        <button type="button" 
                                                id="btn_copy_{{ $fighter->id }}"
                                                class="btn btn-sm btn-outline-primary font-monospace py-1.5 px-3 text-nowrap" 
                                                style="font-size: 11px;" 
                                                onclick="copyPrompt('{{ $fighter->id }}')">
                                            <i class="bi bi-clipboard me-1"></i> Copier
                                        </button>
                                    </td>

                                    <!-- Statut -->
                                    <td>
                                        @if($fighter->portrait_status === 'pending')
                                            <span class="badge bg-warning text-dark font-monospace" style="font-size: 10px;">
                                                <i class="bi bi-hourglass-split me-1"></i> En Attente
                                            </span>
                                        @else
                                            <span class="badge bg-success font-monospace" style="font-size: 10px;">
                                                <i class="bi bi-check2-circle me-1"></i> Livré
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Colonne Action : Téléverser & Publier le portrait -->
                                    <td class="text-end pe-4">
                                        @if($fighter->portrait_path)
                                            <div class="d-flex align-items-center justify-content-end gap-2 mb-2">
                                                <img src="{{ $fighter->portrait_url }}" alt="Portrait Actuel" class="rounded border shadow-sm" style="width: 38px; height: 38px; object-fit: cover;">
                                                <a href="{{ route('fighters.show', $fighter) }}" target="_blank" class="btn btn-xs btn-outline-secondary py-0 px-1 font-monospace" style="font-size: 10px;" title="Voir la carte du joueur">
                                                    <i class="bi bi-box-arrow-up-right me-0.5"></i> Carte
                                                </a>
                                            </div>
                                        @endif

                                        <form method="POST" action="{{ route('admin.portraits.upload', $fighter) }}" enctype="multipart/form-data" class="d-flex align-items-center gap-1.5 justify-content-end">
                                            @csrf
                                            <input type="file" name="portrait_file" class="form-control form-control-sm" accept="image/*" required style="max-width: 160px; font-size: 11px;">
                                            <button type="submit" class="btn btn-sm {{ $fighter->portrait_status === 'pending' ? 'btn-danger' : 'btn-outline-danger' }} font-monospace text-nowrap" style="font-size: 11px;">
                                                <i class="bi bi-cloud-arrow-up-fill me-1"></i> {{ $fighter->portrait_status === 'pending' ? 'Publier' : 'Remplacer' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted font-monospace">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                        Aucune commande de portrait trouvée pour ce filtre.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($fighters->hasPages())
                    <div class="card-footer bg-white py-3 d-flex justify-content-center border-0">
                        {{ $fighters->appends(['status' => $currentStatus])->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyPrompt(fighterId) {
    const textarea = document.getElementById('prompt_text_' + fighterId);
    const btn = document.getElementById('btn_copy_' + fighterId);
    if (!textarea) return;

    textarea.classList.remove('d-none');
    textarea.select();
    textarea.setSelectionRange(0, 99999);

    navigator.clipboard.writeText(textarea.value).then(() => {
        textarea.classList.add('d-none');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg text-success me-1"></i> Copié !';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success', 'text-white');
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success', 'text-white');
            btn.classList.add('btn-outline-primary');
        }, 2000);
    }).catch(err => {
        document.execCommand('copy');
        textarea.classList.add('d-none');
        alert('Prompt copié dans le presse-papier !');
    });
}
</script>
@endpush
@endsection
