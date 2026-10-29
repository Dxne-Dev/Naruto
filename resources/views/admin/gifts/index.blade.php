@extends('layouts.admin')

@section('title', 'Cadeaux Admin & Économie Joueurs')

@section('content')
<div class="container-fluid content-inner pb-0">
    <!-- Header Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-4 bg-white border-0 shadow-sm rounded-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <span class="badge bg-primary-subtle text-primary font-monospace px-3 py-1 mb-2">
                            <i class="bi bi-gift-fill me-1"></i> GESTION DES RÉCOMPENSES & PACTES
                        </span>
                        <h2 class="fw-bold mb-1" style="letter-spacing: -0.02em;">Espace Cadeaux & Privilèges Joueurs</h2>
                        <p class="text-muted mb-0 small">
                            Pilote l'économie des joueurs réels : bascule les Pass Shinobi sans passer par Stripe/Chariow, injecte de l'XP de combat et distribue des jetons de roue.
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-people me-1"></i> Annuaire Joueurs
                        </a>
                        <a href="{{ route('admin.portraits.index') }}" class="btn btn-outline-danger">
                            <i class="bi bi-magic me-1"></i> Atelier Portraits
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Data Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-white border-0 shadow-sm rounded-3 overflow-hidden">
                <!-- Card Header with Search & Stats -->
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="fw-bold mb-0 text-dark">Joueurs Réels ({{ $stats['total_real_players'] }})</h5>
                        <div class="d-flex gap-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">
                                <i class="bi bi-patch-check-fill me-1"></i> {{ $stats['total_pass_holders'] }} Pass Actifs
                            </span>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace">
                                <i class="bi bi-coin me-1"></i> {{ $stats['total_tokens_circulating'] }} Jetons
                            </span>
                        </div>
                    </div>

                    <!-- Search Form -->
                    <form method="GET" action="{{ route('admin.gifts.index') }}" class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: 280px;">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="q" value="{{ $search }}" class="form-control bg-light border-start-0" placeholder="Pseudo, email, shinobi...">
                            @if($search)
                                <a href="{{ route('admin.gifts.index') }}" class="btn btn-outline-secondary" title="Effacer la recherche">
                                    <i class="bi bi-x"></i>
                                </a>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary px-3">Filtrer</button>
                    </form>
                </div>

                <div class="card-body p-0">
                    @if($users->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            <span>Aucun joueur réel trouvé.</span>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light font-monospace small text-uppercase text-secondary">
                                    <tr>
                                        <th class="ps-4" style="width: 240px;">Joueur (Pilote)</th>
                                        <th style="width: 220px;">Combattant Shinobi</th>
                                        <th style="width: 140px;">Statut Pass</th>
                                        <th style="width: 130px;">Solde Jetons</th>
                                        <th style="min-width: 240px;">Injecter de l'XP</th>
                                        <th class="text-end pe-4" style="min-width: 220px;">Donner des Jetons</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $u)
                                        <tr>
                                            <!-- Joueur (Pilote) -->
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 36px; height: 36px; font-size: 13px;">
                                                        {{ strtoupper(substr($u->username ?? $u->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark fs-6">{{ $u->username }}</div>
                                                        <div class="font-monospace text-muted" style="font-size: 11px;">
                                                            {{ $u->email }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Combattant Shinobi -->
                                            <td>
                                                @if($u->fighter)
                                                    <div class="fw-bold text-dark">{{ $u->fighter->name }}</div>
                                                    <div class="d-flex align-items-center gap-1.5 mt-0.5">
                                                        <span class="badge bg-danger-subtle text-danger font-monospace" style="font-size: 10px;">
                                                            Clan {{ $u->fighter->clan }}
                                                        </span>
                                                        <span class="badge bg-light text-dark border font-monospace" style="font-size: 10px;">
                                                            Niv. {{ $u->fighter->level }}
                                                        </span>
                                                        <span class="font-monospace text-muted" style="font-size: 10px;">
                                                            ({{ number_format($u->fighter->xp, 0, ',', ' ') }} XP)
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 10px;">
                                                        Aucun Shinobi créé
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Statut Pass & Toggle Switch -->
                                            <td>
                                                @if($u->has_paid_microtransaction)
                                                    <div class="d-flex flex-column gap-1">
                                                        <span class="badge bg-success text-white font-monospace d-inline-flex align-items-center gap-1 w-auto py-1">
                                                            <i class="bi bi-patch-check-fill"></i> Pass Actif
                                                        </span>
                                                        <form method="POST" action="{{ route('admin.gifts.toggle-pass', $u) }}" class="m-0" onsubmit="return confirm('Désactiver le Pass Shinobi de {{ $u->username }} ?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-xs btn-outline-danger font-monospace py-0 px-2" style="font-size: 10px;">
                                                                <i class="bi bi-x-circle me-0.5"></i> Révoquer
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <div class="d-flex flex-column gap-1">
                                                        <span class="badge bg-secondary-subtle text-secondary border font-monospace d-inline-flex align-items-center gap-1 w-auto py-1">
                                                            <i class="bi bi-hourglass-split"></i> Quota 2 Tournois
                                                        </span>
                                                        <form method="POST" action="{{ route('admin.gifts.toggle-pass', $u) }}" class="m-0" onsubmit="return confirm('Activer immédiatement le Pass Shinobi Illimité pour {{ $u->username }} ?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-xs btn-success font-monospace fw-bold py-0.5 px-2 text-nowrap" style="font-size: 10px;">
                                                                <i class="bi bi-lightning-charge-fill me-0.5"></i> Activer Pass
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </td>

                                            <!-- Solde Jetons -->
                                            <td>
                                                <div class="d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-2 bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace fw-bold" style="font-size: 12px;">
                                                    <i class="bi bi-coin text-warning"></i>
                                                    <span>{{ $u->wheel_tokens }} jetons</span>
                                                </div>
                                            </td>

                                            <!-- Injecter de l'XP -->
                                            <td>
                                                @if($u->fighter)
                                                    <form method="POST" action="{{ route('admin.gifts.send-xp', $u) }}" class="d-flex align-items-center gap-1.5">
                                                        @csrf
                                                        <div class="input-group input-group-sm" style="max-width: 140px;">
                                                            <input type="number" name="amount" min="1" max="50000" step="50" value="500" required class="form-control form-control-sm text-center font-monospace fw-bold bg-light">
                                                            <span class="input-group-text bg-white font-monospace text-muted" style="font-size: 10px;">XP</span>
                                                        </div>
                                                        <button type="submit" class="btn btn-sm btn-primary font-monospace fw-bold px-2.5 py-1 d-inline-flex align-items-center gap-1" title="Injecter l'XP">
                                                            <i class="bi bi-lightning-fill"></i>
                                                            <span>Donner</span>
                                                        </button>
                                                    </form>
                                                    <div class="d-flex gap-1 mt-1">
                                                        <button type="button" class="badge bg-light text-muted border text-decoration-none cursor-pointer" onclick="this.closest('td').querySelector('input[name=amount]').value = 250;">+250</button>
                                                        <button type="button" class="badge bg-light text-muted border text-decoration-none cursor-pointer" onclick="this.closest('td').querySelector('input[name=amount]').value = 500;">+500 (1 lvl)</button>
                                                        <button type="button" class="badge bg-light text-muted border text-decoration-none cursor-pointer" onclick="this.closest('td').querySelector('input[name=amount]').value = 1500;">+1500</button>
                                                    </div>
                                                @else
                                                    <span class="text-muted small fst-italic">Sans combattant</span>
                                                @endif
                                            </td>

                                            <!-- Donner des Jetons -->
                                            <td class="text-end pe-4">
                                                <form method="POST" action="{{ route('admin.gifts.send-tokens', $u) }}" class="d-inline-flex align-items-center justify-content-end gap-1.5">
                                                    @csrf
                                                    <div class="input-group input-group-sm" style="max-width: 120px;">
                                                        <input type="number" name="amount" min="-100" max="100" value="3" required class="form-control form-control-sm text-center font-monospace fw-bold bg-light">
                                                        <span class="input-group-text bg-white font-monospace text-warning-emphasis" style="font-size: 11px;">
                                                            <i class="bi bi-coin"></i>
                                                        </span>
                                                    </div>
                                                    <button type="submit" class="btn btn-sm btn-warning text-dark font-monospace fw-bold px-2.5 py-1 d-inline-flex align-items-center gap-1" title="Appliquer les jetons">
                                                        <i class="bi bi-plus-circle-fill"></i>
                                                        <span>Ajouter</span>
                                                    </button>
                                                </form>
                                                <div class="d-flex justify-content-end gap-1 mt-1">
                                                    <button type="button" class="badge bg-light text-muted border text-decoration-none cursor-pointer" onclick="this.closest('td').querySelector('input[name=amount]').value = 1;">+1</button>
                                                    <button type="button" class="badge bg-light text-muted border text-decoration-none cursor-pointer" onclick="this.closest('td').querySelector('input[name=amount]').value = 5;">+5</button>
                                                    <button type="button" class="badge bg-light text-muted border text-decoration-none cursor-pointer" onclick="this.closest('td').querySelector('input[name=amount]').value = 10;">+10</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($users, 'links'))
                            <div class="p-3 border-top d-flex justify-content-end bg-light">
                                {{ $users->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
