@extends('layouts.admin')

@section('title', 'Gestion des Tournois')
@section('page_title', 'Gestion des Tournois & Examens Chūnin')
@section('page_subtitle', 'Création, arbitrage, injection des bots et résolution des brackets.')

@section('page_actions')
<a href="{{ route('admin.tournaments.create') }}" class="btn btn-primary d-flex align-items-center gap-1">
    <i class="bi bi-plus-lg"></i>
    <span>Nouveau Tournoi</span>
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Tous les Tournois Enregistrés</h4>
                <span class="badge bg-primary">{{ $tournaments->total() ?? $tournaments->count() }} tournois</span>
            </div>
            <div class="card-body px-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width: 70px;">ID</th>
                                <th>TITRE</th>
                                <th>STATUT</th>
                                <th>INSCRITS</th>
                                <th>DÉBUT PROGRAMMÉ</th>
                                <th class="text-end">ACTIONS D'ARBITRAGE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tournaments as $t)
                                <tr>
                                    <td class="fw-bold">#{{ $t->id }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $t->title }}</div>
                                        <small class="text-muted">Type : {{ ucfirst($t->type) }}</small>
                                    </td>
                                    <td>
                                        @if($t->status === 'open' || $t->status === 'registration')
                                            <span class="badge bg-success">Inscriptions Ouvertes</span>
                                        @elseif($t->status === 'in_progress')
                                            <span class="badge bg-warning text-dark">En Cours</span>
                                        @elseif($t->status === 'completed')
                                            <span class="badge bg-secondary">Terminé</span>
                                        @else
                                            <span class="badge bg-light text-dark">{{ strtoupper($t->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold">
                                            {{ $t->entries_count }}/{{ $t->max_participants ?? 8 }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">{{ $t->starts_at ? $t->starts_at->format('d/m/Y H:i') : 'Non défini' }}</div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('admin.tournaments.edit', $t) }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                                                <i class="bi bi-gear-fill"></i>
                                                <span>Gérer</span>
                                            </a>

                                            @if($t->status !== 'completed')
                                                <form method="POST" action="{{ route('admin.tournaments.end', $t) }}" class="d-inline" onsubmit="return confirm('Mettre fin immédiatement à ce tournoi ?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                                        Clôturer
                                                    </button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('admin.tournaments.destroy', $t) }}" class="d-inline" onsubmit="return confirm('Supprimer définitivement ce tournoi ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        Aucun tournoi trouvé dans la base de données.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($tournaments, 'links'))
                    <div class="p-3">
                        {{ $tournaments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
