@extends('layouts.admin')

@section('title', 'Tableau de Bord Game Master')
@section('page_title', 'Tableau de Bord Game Master')
@section('page_subtitle', 'Supervision des tournois, arbitrage en direct et gestion des combattants.')

@section('page_actions')
<div class="d-flex gap-2">
    <a href="{{ route('admin.tournaments.create') }}" class="btn btn-primary d-flex align-items-center gap-1">
        <i class="bi bi-plus-lg"></i>
        <span>Nouvel Examen Chūnin</span>
    </a>
    <a href="{{ route('admin.users.index') }}" class="btn btn-light d-flex align-items-center gap-1">
        <i class="bi bi-people-fill"></i>
        <span>Annuaire Joueurs</span>
    </a>
</div>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <!-- KPI 1 : Tournois Actifs -->
    <div class="col-md-6 col-lg-3">
        <div class="card card-kpi-custom p-3 bg-white h-100">
            <div class="card-body p-2 d-flex justify-content-between align-items-center">
                <div>
                    <div class="kpi-title text-primary">Tournois Actifs</div>
                    <div class="kpi-number text-dark my-1">{{ $activeTournamentsCount }}</div>
                    <div class="kpi-subtext">Inscriptions ou en cours</div>
                </div>
                <div class="rounded-3 p-3 text-primary" style="background-color: #EFF6FF;">
                    <i class="bi bi-trophy-fill fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 2 : Participants Réels -->
    <div class="col-md-6 col-lg-3">
        <div class="card card-kpi-custom p-3 bg-white h-100">
            <div class="card-body p-2 d-flex justify-content-between align-items-center">
                <div>
                    <div class="kpi-title text-success">Joueurs Inscrits</div>
                    <div class="kpi-number text-dark my-1">{{ $realParticipantsCount }}</div>
                    <div class="kpi-subtext">Participants réels</div>
                </div>
                <div class="rounded-3 p-3 text-success" style="background-color: #F0FDF4;">
                    <i class="bi bi-person-check-fill fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 3 : Bots Arbitrage -->
    <div class="col-md-6 col-lg-3">
        <div class="card card-kpi-custom p-3 bg-white h-100">
            <div class="card-body p-2 d-flex justify-content-between align-items-center">
                <div>
                    <div class="kpi-title text-warning">Bots Injectés</div>
                    <div class="kpi-number text-dark my-1">{{ $botParticipantsCount }}</div>
                    <div class="kpi-subtext">PNJ de comblement à T-5</div>
                </div>
                <div class="rounded-3 p-3 text-warning" style="background-color: #FEFCE8;">
                    <i class="bi bi-robot fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 4 : Total Inscrits & Combattants -->
    <div class="col-md-6 col-lg-3">
        <div class="card card-kpi-custom p-3 bg-white h-100">
            <div class="card-body p-2 d-flex justify-content-between align-items-center">
                <div>
                    <div class="kpi-title text-danger">Shinobis Créés</div>
                    <div class="kpi-number text-dark my-1">{{ $totalFightersCount }}</div>
                    <div class="kpi-subtext">{{ $totalUsersCount }} comptes joueurs</div>
                </div>
                <div class="rounded-3 p-3 text-danger" style="background-color: #FEF2F2;">
                    <i class="bi bi-fire fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table des Derniers Tournois -->
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="header-title">
                    <h4 class="card-title mb-0">Tournois & Remplissage en Temps Réel</h4>
                </div>
                <a href="{{ route('admin.tournaments.index') }}" class="btn btn-sm btn-outline-primary">
                    Voir Tous les Tournois <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body px-0">
                @if($recentTournaments->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x display-4 text-muted opacity-50 mb-3 d-block"></i>
                        <h5>Aucun tournoi enregistré pour l'instant</h5>
                        <p class="small text-muted">Crée ton premier tournoi en cliquant sur « Nouvel Examen Chūnin » ci-dessus.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>TITRE</th>
                                    <th>STATUT</th>
                                    <th>REMPLISSAGE</th>
                                    <th>CLÔTURE</th>
                                    <th class="text-end">ACTIONS DIRECTES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTournaments as $tournament)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $tournament->title }}</div>
                                            <small class="text-muted">Type : {{ ucfirst($tournament->type) }} ({{ $tournament->max_participants }} places)</small>
                                        </td>
                                        <td>
                                            @if($tournament->status === 'open')
                                                <span class="badge bg-success">Inscriptions Ouvertes</span>
                                            @elseif($tournament->status === 'in_progress')
                                                <span class="badge bg-warning text-dark">En Cours</span>
                                            @elseif($tournament->status === 'completed')
                                                <span class="badge bg-secondary">Terminé</span>
                                            @else
                                                <span class="badge bg-light text-dark">{{ ucfirst($tournament->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $count = $tournament->entries_count ?? $tournament->entries()->count();
                                                $percent = min(100, round(($count / $tournament->max_participants) * 100));
                                            @endphp
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="small font-weight-bold">{{ $count }}/{{ $tournament->max_participants }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small">{{ $tournament->registration_closes_at->format('d/m/Y H:i') }}</div>
                                            <small class="text-muted">{{ $tournament->registration_closes_at->diffForHumans() }}</small>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <a href="{{ route('admin.tournaments.edit', $tournament) }}" class="btn btn-sm btn-icon btn-primary" title="Modifier & Arbitrer">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <a href="{{ route('tournaments.show', $tournament) }}" class="btn btn-sm btn-icon btn-info" title="Voir l'Arène Publique" target="_blank">
                                                    <i class="bi bi-eye-fill"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
