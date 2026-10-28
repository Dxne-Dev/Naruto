@extends('layouts.admin')

@section('title', 'Validation des Demandes de Pass Shinobi')
@section('page_title', 'Validation des Demandes de Pass')
@section('page_subtitle', 'Vérification manuelle des paiements de 700 FCFA effectués par les joueurs.')

@section('content')
<div class="row g-3 mb-4">
    <!-- Stat 1 : En attente -->
    <div class="col-md-4">
        <a href="{{ route('admin.pass-requests.index', ['status' => 'pending']) }}" class="text-decoration-none">
            <div class="card card-kpi-custom p-3 bg-white h-100 {{ $status === 'pending' ? 'border-primary' : '' }}">
                <div class="card-body p-2 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title text-warning">En attente de vérification</div>
                        <div class="kpi-number text-dark my-1">{{ $counts['pending'] }}</div>
                        <div class="kpi-subtext">À traiter par l'arbitre</div>
                    </div>
                    <div class="rounded-3 p-3 text-warning" style="background-color: #FEF3C7;">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Stat 2 : Approuvées -->
    <div class="col-md-4">
        <a href="{{ route('admin.pass-requests.index', ['status' => 'approved']) }}" class="text-decoration-none">
            <div class="card card-kpi-custom p-3 bg-white h-100 {{ $status === 'approved' ? 'border-primary' : '' }}">
                <div class="card-body p-2 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title text-success">Pass Débloqués & Validés</div>
                        <div class="kpi-number text-dark my-1">{{ $counts['approved'] }}</div>
                        <div class="kpi-subtext">Demandes confirmées</div>
                    </div>
                    <div class="rounded-3 p-3 text-success" style="background-color: #D1FAE5;">
                        <i class="bi bi-patch-check-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Stat 3 : Rejetées -->
    <div class="col-md-4">
        <a href="{{ route('admin.pass-requests.index', ['status' => 'rejected']) }}" class="text-decoration-none">
            <div class="card card-kpi-custom p-3 bg-white h-100 {{ $status === 'rejected' ? 'border-primary' : '' }}">
                <div class="card-body p-2 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title text-danger">Demandes Rejetées</div>
                        <div class="kpi-number text-dark my-1">{{ $counts['rejected'] }}</div>
                        <div class="kpi-subtext">Non vérifiées / refusées</div>
                    </div>
                    <div class="rounded-3 p-3 text-danger" style="background-color: #FEE2E2;">
                        <i class="bi bi-x-octagon-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-4 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show border-0 rounded-3 mb-4 shadow-sm" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i> {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0 fw-bold">
            Liste des Demandes — 
            <span class="text-capitalize badge {{ $status === 'pending' ? 'bg-warning text-dark' : ($status === 'approved' ? 'bg-success' : 'bg-danger') }}">
                {{ $status === 'pending' ? 'En attente' : ($status === 'approved' ? 'Validées' : 'Rejetées') }}
            </span>
        </h5>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('admin.pass-requests.index', ['status' => 'pending']) }}" class="btn btn-outline-secondary {{ $status === 'pending' ? 'active' : '' }}">En attente</a>
            <a href="{{ route('admin.pass-requests.index', ['status' => 'approved']) }}" class="btn btn-outline-secondary {{ $status === 'approved' ? 'active' : '' }}">Validées</a>
            <a href="{{ route('admin.pass-requests.index', ['status' => 'rejected']) }}" class="btn btn-outline-secondary {{ $status === 'rejected' ? 'active' : '' }}">Rejetées</a>
            <a href="{{ route('admin.pass-requests.index', ['status' => 'all']) }}" class="btn btn-outline-secondary {{ $status === 'all' ? 'active' : '' }}">Toutes</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase font-mono small text-muted">
                    <tr>
                        <th class="ps-4">Joueur & Shinobi</th>
                        <th>Moyen & Coordonnées</th>
                        <th>Réf. Transaction</th>
                        <th>Note Joueur</th>
                        <th>Date Demande</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions Arbitre</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <!-- Joueur -->
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-40 rounded-circle bg-light border flex-shrink-0 d-flex align-items-center justify-center font-bold text-dark">
                                        {{ strtoupper(substr($req->user->username ?? $req->user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $req->user->username ?? $req->user->name }}</div>
                                        <div class="text-muted small font-mono">{{ $req->user->email }}</div>
                                        @if($req->user->fighter)
                                            <span class="badge bg-light text-dark border font-mono" style="font-size: 10px;">
                                                Shinobi : {{ $req->user->fighter->name }} (Niv. {{ $req->user->fighter->level }})
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Moyen & Coordonnées -->
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-mono mb-1 d-inline-block">
                                    {{ $req->method_label }}
                                </span>
                                <div class="fw-bold font-mono text-dark small">
                                    <i class="bi bi-telephone-fill me-1 text-muted"></i> {{ $req->phone_or_card }}
                                </div>
                            </td>

                            <!-- Réf Transaction -->
                            <td>
                                @if($req->transaction_reference)
                                    <code class="bg-light px-2 py-1 rounded text-dark font-mono small border">
                                        {{ $req->transaction_reference }}
                                    </code>
                                @else
                                    <span class="text-muted small italic">Non renseignée</span>
                                @endif
                            </td>

                            <!-- Note Joueur -->
                            <td style="max-width: 220px;">
                                @if($req->user_note)
                                    <span class="small text-muted d-block text-truncate" title="{{ $req->user_note }}">
                                        {{ $req->user_note }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            <!-- Date -->
                            <td class="font-mono small text-muted">
                                <div>{{ $req->created_at->format('d/m/Y H:i') }}</div>
                                <div style="font-size: 11px;">{{ $req->created_at->diffForHumans() }}</div>
                            </td>

                            <!-- Statut -->
                            <td>
                                @if($req->status === 'pending')
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-clock me-1"></i> En Attente
                                    </span>
                                @elseif($req->status === 'approved')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i> Pass Débloqué
                                    </span>
                                    @if($req->processor)
                                        <div class="text-muted small" style="font-size: 10px;">par {{ $req->processor->username }}</div>
                                    @endif
                                @elseif($req->status === 'rejected')
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle me-1"></i> Rejetée
                                    </span>
                                    @if($req->admin_rejection_reason)
                                        <div class="text-danger small mt-1 font-mono" style="font-size: 11px;" title="{{ $req->admin_rejection_reason }}">
                                            Motif : {{ Str::limit($req->admin_rejection_reason, 30) }}
                                        </div>
                                    @endif
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1">
                                    @if($req->status === 'pending')
                                        <!-- Bouton Activer -->
                                        <form method="POST" action="{{ route('admin.pass-requests.approve', $req) }}" onsubmit="return confirm('Confirmer l\'activation du Pass Shinobi Illimité pour {{ $req->user->username }} ?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success d-flex align-items-center gap-1" title="Valider le paiement et activer le pass">
                                                <i class="bi bi-check-lg"></i>
                                                <span>Activer le Pass</span>
                                            </button>
                                        </form>

                                        <!-- Bouton Rejeter (Modal) -->
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $req->id }}" title="Rejeter la demande">
                                            <i class="bi bi-x-lg"></i>
                                            <span>Rejeter</span>
                                        </button>
                                    @endif

                                    <!-- Bouton Supprimer / Archiver -->
                                    <form method="POST" action="{{ route('admin.pass-requests.destroy', $req) }}" onsubmit="return confirm('Supprimer définitivement cette demande de l\'historique ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-muted" title="Supprimer de l'historique">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Modale Rejet avec Motif -->
                                @if($req->status === 'pending')
                                    <div class="modal fade text-start" id="rejectModal-{{ $req->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form method="POST" action="{{ route('admin.pass-requests.reject', $req) }}">
                                                    @csrf
                                                    <div class="modal-header bg-danger-subtle border-0">
                                                        <h6 class="modal-title fw-bold text-danger">
                                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Rejeter la demande de {{ $req->user->username }}
                                                        </h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <p class="small text-muted mb-3">
                                                            Indique le motif du rejet. Ce texte sera affiché directement au joueur dans sa fenêtre de notification afin qu'il puisse corriger sa démarche.
                                                        </p>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">Motif du refus *</label>
                                                            <textarea name="admin_rejection_reason" class="form-control" rows="3" required placeholder="Exemple : Aucune transaction reçue avec ce numéro sur Wave à cette heure. Merci de vérifier le numéro débité ou de relancer le paiement direct."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 bg-light">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-danger btn-sm font-mono">Confirmer le rejet & Notifier</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                Aucune demande enregistrée dans cette section.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="p-3 border-top">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
