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
                            Génère les portraits sur ChatGPT/DALL-E avec le prompt dynamique et téléverse le fichier pour l'injecter sur la carte du joueur.
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

    <!-- Portraits Orders List -->
    <div class="row">
        <div class="col-12">
            @forelse($fighters as $fighter)
                <div class="card card-kpi-custom mb-4 bg-white border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $fighter->portrait_status === 'pending' ? 'bg-warning text-dark' : 'bg-success' }}">
                                {{ $fighter->portrait_status === 'pending' ? 'EN ATTENTE DE FORGE' : 'LIVRÉ & PUBLIÉ' }}
                            </span>
                            <strong class="fs-6">{{ $fighter->name }}</strong>
                            <span class="text-muted small">&bull; Joueur : {{ $fighter->user?->name }} ({{ $fighter->user?->email }})</span>
                        </div>
                        <div class="text-muted font-monospace small">
                            Commandé le : {{ $fighter->portrait_ordered_at?->format('d/m/Y H:i') ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-4 align-items-center">
                            <!-- Photo Source Utilisateur -->
                            <div class="col-12 col-md-3 text-center">
                                <span class="d-block text-muted font-monospace small mb-2 text-uppercase fw-bold">Photo Source Envoyée</span>
                                @if($fighter->source_photo_path)
                                    <div class="position-relative mx-auto rounded-3 overflow-hidden border shadow-sm" style="max-width: 180px; height: 180px; background: #000;">
                                        <img src="{{ $fighter->source_photo_path }}" alt="Photo Source" class="w-100 h-100 object-fit-cover">
                                    </div>
                                    <a href="{{ $fighter->source_photo_path }}" target="_blank" download class="btn btn-sm btn-outline-dark mt-2 font-monospace" style="font-size: 11px;">
                                        <i class="bi bi-download me-1"></i> Télécharger Photo
                                    </a>
                                @else
                                    <div class="p-4 bg-light rounded text-muted font-monospace small">
                                        Pas de photo personnalisée (Avatar par défaut)
                                    </div>
                                @endif
                            </div>

                            <!-- Le Prompt Dynamique Prêt pour ChatGPT -->
                            <div class="col-12 col-md-5">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted font-monospace small text-uppercase fw-bold">Prompt IA Prêt pour ChatGPT / Muse</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary font-monospace py-1 px-2" style="font-size: 11px;" onclick="copyPrompt('prompt_{{ $fighter->id }}')">
                                        <i class="bi bi-clipboard me-1"></i> Copier Prompt
                                    </button>
                                </div>
                                <textarea id="prompt_{{ $fighter->id }}" class="form-control font-monospace small bg-light" rows="5" readonly style="font-size: 12px; resize: none;">{{ $fighter->generated_prompt }}</textarea>
                                
                                <div class="mt-2 text-muted font-monospace" style="font-size: 11px;">
                                    <strong>Affiliation :</strong> {{ $fighter->village }} &bull; Clan {{ $fighter->clan }} &bull; <strong>Affinité :</strong> {{ $fighter->primary_element }}
                                </div>
                            </div>

                            <!-- Upload du Portrait Final par l'Admin -->
                            <div class="col-12 col-md-4">
                                <div class="p-3 bg-light rounded-3 border">
                                    <span class="d-block text-dark font-monospace small mb-2 text-uppercase fw-bold">
                                        {{ $fighter->portrait_status === 'pending' ? '1. Injecter le Portrait Généré' : 'Portrait Actuel en Ligne' }}
                                    </span>

                                    @if($fighter->portrait_path)
                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <img src="{{ $fighter->portrait_path }}" alt="Portrait Final" class="rounded border shadow-sm" style="width: 70px; height: 70px; object-fit: cover;">
                                            <div>
                                                <span class="badge bg-success-subtle text-success mb-1">En ligne sur sa carte</span>
                                                <a href="{{ route('fighters.show', $fighter) }}" target="_blank" class="d-block small text-primary text-decoration-none font-monospace">
                                                    Voir sa carte Databook &rarr;
                                                </a>
                                            </div>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('admin.portraits.upload', $fighter) }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-2">
                                            <input type="file" name="portrait_file" class="form-control form-control-sm" accept="image/*" required>
                                        </div>
                                        <button type="submit" class="btn btn-danger btn-sm w-100 font-monospace text-uppercase">
                                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> 
                                            {{ $fighter->portrait_status === 'pending' ? 'Publier sur sa Carte Ninja' : 'Remplacer l\'image en ligne' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card card-kpi-custom p-5 text-center bg-white border-0 shadow-sm">
                    <div class="text-muted font-monospace py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                        Aucune commande de portrait trouvée pour ce filtre.
                    </div>
                </div>
            @endforelse

            <div class="d-flex justify-content-center mt-4">
                {{ $fighters->appends(['status' => $currentStatus])->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyPrompt(id) {
    const textarea = document.getElementById(id);
    textarea.select();
    navigator.clipboard.writeText(textarea.value);
    alert('Prompt ninja copié dans le presse-papier ! Tu peux le coller directement dans ChatGPT avec la photo du joueur.');
}
</script>
@endpush
@endsection
