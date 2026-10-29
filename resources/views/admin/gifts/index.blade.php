@extends('layouts.admin')

@section('title', 'Cadeaux Admin & Économie Joueurs')
@section('page_title', 'Espace Cadeaux Admin')
@section('page_subtitle', 'Distribution de jetons, injection d\'XP et activation directe du Pass Shinobi Illimité.')

@section('page_actions')
<div class="d-flex gap-2">
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-light d-flex align-items-center gap-1">
        <i class="bi bi-people"></i>
        <span>Annuaire Joueurs</span>
    </a>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-light d-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i>
        <span>Tableau de Bord</span>
    </a>
</div>
@endsection

@section('content')
<!-- KPI Row -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
                <div>
                    <div class="kpi-title">Joueurs Réels</div>
                    <div class="kpi-number text-dark">{{ $stats['total_real_players'] }}</div>
                    <div class="kpi-subtext">Comptes hors administration</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 bg-success bg-opacity-10 text-success">
                    <i class="bi bi-patch-check-fill fs-3"></i>
                </div>
                <div>
                    <div class="kpi-title">Pass Actifs</div>
                    <div class="kpi-number text-success">{{ $stats['total_pass_holders'] }}</div>
                    <div class="kpi-subtext">Accès illimité à vie</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-coin fs-3"></i>
                </div>
                <div>
                    <div class="kpi-title">Jetons en Circulation</div>
                    <div class="kpi-number text-warning">{{ $stats['total_tokens_circulating'] }}</div>
                    <div class="kpi-subtext">Roulette & relance d'affinité</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="card-title mb-0 fw-bold">Distribution des Récompenses & Pass</h5>
                    <small class="text-muted">Sélectionne un joueur pour lui injecter de l'XP, lui octroyer des jetons ou modifier son Pass.</small>
                </div>
                
                <!-- Search Form -->
                <form method="GET" action="{{ route('admin.gifts.index') }}" class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q" value="{{ $search }}" class="form-control bg-light border-start-0" placeholder="Pseudo, email, shinobi..." style="min-width: 220px;">
                        @if($search)
                            <a href="{{ route('admin.gifts.index') }}" class="btn btn-outline-secondary" title="Effacer la recherche">
                                <i class="bi bi-x"></i>
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                </form>
            </div>

            <div class="card-body p-0">
                @if($users->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                        <span>Aucun joueur réel trouvé correspondant à ta recherche.</span>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>JOUEUR (PILOTE)</th>
                                    <th>SHINOBI & NIVEAU</th>
                                    <th>SOLDE JETONS</th>
                                    <th>PASS SHINOBI</th>
                                    <th class="text-center" style="min-width: 220px;">INJECTER DE L'XP</th>
                                    <th class="text-center" style="min-width: 180px;">DONNER DES JETONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $u)
                                    <tr>
                                        <!-- Joueur -->
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 14px;">
                                                    {{ strtoupper(substr($u->username ?? $u->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $u->username }}</div>
                                                    <small class="text-muted d-block">{{ $u->email }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Shinobi -->
                                        <td>
                                            @if($u->fighter)
                                                <div class="fw-bold text-primary">{{ $u->fighter->name }}</div>
                                                <div class="small text-muted font-mono">
                                                    Clan {{ $u->fighter->clan }} • Niv. {{ $u->fighter->level }} • <span class="text-dark fw-bold">{{ $u->fighter->xp }} XP</span>
                                                </div>
                                            @else
                                                <span class="badge bg-light text-muted border">Aucun Shinobi créé</span>
                                            @endif
                                        </td>

                                        <!-- Solde Jetons -->
                                        <td>
                                            <div class="d-flex align-items-center gap-1.5">
                                                <span class="badge bg-warning text-dark font-mono fs-6 px-2.5 py-1">
                                                    <i class="bi bi-coin"></i> {{ $u->wheel_tokens }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Statut Pass & Toggle -->
                                        <td>
                                            <div class="d-flex flex-column gap-1.5 align-items-start">
                                                @if($u->has_paid_microtransaction)
                                                    <span class="badge bg-success-subtle text-success border border-success d-inline-flex align-items-center gap-1">
                                                        <i class="bi bi-check-circle-fill"></i> Pass Actif (Illimité)
                                                    </span>
                                                    <form method="POST" action="{{ route('admin.gifts.toggle-pass', $u) }}" class="m-0" onsubmit="return confirm('Désactiver le Pass Shinobi de {{ $u->username }} ?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-link text-danger p-0 text-decoration-none small font-mono" style="font-size: 11px;">
                                                            <i class="bi bi-x-circle"></i> Désactiver
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary d-inline-flex align-items-center gap-1">
                                                        <i class="bi bi-hourglass-split"></i> Quota Gratuit (2 Tournois)
                                                    </span>
                                                    <form method="POST" action="{{ route('admin.gifts.toggle-pass', $u) }}" class="m-0" onsubmit="return confirm('Activer gratuitement le Pass Shinobi Illimité pour {{ $u->username }} ?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success py-0 px-2 fw-bold text-nowrap" style="font-size: 11px;">
                                                            <i class="bi bi-lightning-charge-fill"></i> Activer le Pass
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Injecter XP -->
                                        <td>
                                            @if($u->fighter)
                                                <form method="POST" action="{{ route('admin.gifts.send-xp', $u) }}" class="d-flex flex-column gap-1.5 align-items-center">
                                                    @csrf
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text bg-light text-muted font-mono" style="font-size: 11px;">XP</span>
                                                        <input type="number" name="amount" min="1" max="50000" step="50" value="500" required class="form-control form-control-sm text-center font-mono fw-bold">
                                                        <button type="submit" class="btn btn-primary btn-sm px-2.5" title="Créditer cette quantité d'XP">
                                                            <i class="bi bi-send-fill"></i>
                                                        </button>
                                                    </div>
                                                    <div class="d-flex gap-1">
                                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1 font-mono" style="font-size: 10px;" onclick="this.closest('form').querySelector('input[name=amount]').value = 250;">+250</button>
                                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1 font-mono" style="font-size: 10px;" onclick="this.closest('form').querySelector('input[name=amount]').value = 500;">+500 (1 lvl)</button>
                                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1 font-mono" style="font-size: 10px;" onclick="this.closest('form').querySelector('input[name=amount]').value = 1500;">+1500</button>
                                                    </div>
                                                </form>
                                            @else
                                                <div class="text-center text-muted small italic">Pas de Shinobi</div>
                                            @endif
                                        </td>

                                        <!-- Donner Jetons -->
                                        <td>
                                            <form method="POST" action="{{ route('admin.gifts.send-tokens', $u) }}" class="d-flex flex-column gap-1.5 align-items-center">
                                                @csrf
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-warning bg-opacity-25 text-warning-emphasis font-mono" style="font-size: 11px;">
                                                        <i class="bi bi-coin"></i>
                                                    </span>
                                                    <input type="number" name="amount" min="-100" max="100" value="3" required class="form-control form-control-sm text-center font-mono fw-bold">
                                                    <button type="submit" class="btn btn-warning btn-sm px-2.5 text-dark fw-bold" title="Appliquer les jetons">
                                                        <i class="bi bi-plus-circle-fill"></i>
                                                    </button>
                                                </div>
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-xs btn-outline-warning py-0 px-1 font-mono text-dark" style="font-size: 10px;" onclick="this.closest('form').querySelector('input[name=amount]').value = 1;">+1</button>
                                                    <button type="button" class="btn btn-xs btn-outline-warning py-0 px-1 font-mono text-dark" style="font-size: 10px;" onclick="this.closest('form').querySelector('input[name=amount]').value = 5;">+5</button>
                                                    <button type="button" class="btn btn-xs btn-outline-warning py-0 px-1 font-mono text-dark" style="font-size: 10px;" onclick="this.closest('form').querySelector('input[name=amount]').value = 10;">+10</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($users, 'links'))
                        <div class="p-3 border-top d-flex justify-content-end">
                            {{ $users->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
