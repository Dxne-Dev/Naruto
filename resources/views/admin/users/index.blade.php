@extends('layouts.admin')

@section('title', 'Annuaire des Joueurs')
@section('page_title', 'Annuaire des Joueurs & Combattants')
@section('page_subtitle', 'Révocation des accès, suspension des comptes et modération des personnages.')

@section('page_actions')
<a href="{{ route('admin.dashboard') }}" class="btn btn-light d-flex align-items-center gap-1">
    <i class="bi bi-arrow-left"></i>
    <span>Retour Tableau de Bord</span>
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Tous les Comptes Enregistrés</h4>
                <span class="badge bg-primary">{{ $users->total() ?? $users->count() }} comptes</span>
            </div>
            <div class="card-body px-0">
                @if($users->isEmpty())
                    <div class="text-center py-5 text-muted">
                        Aucun joueur enregistré pour l'instant.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>JOUEUR (PILOTE)</th>
                                    <th>EMAIL</th>
                                    <th>IP / EMPREINTE</th>
                                    <th>SHINOBI ASSOCIÉ</th>
                                    <th>STATUT PASS</th>
                                    <th>ÉTAT DU COMPTE</th>
                                    <th class="text-end">ACTIONS & SANCTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $u)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $u->username }}</div>
                                            <small class="text-muted">{{ $u->name }}</small>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $u->email }}</small>
                                        </td>
                                        <td>
                                            <div class="small">{{ $u->registration_ip ?? '127.0.0.1' }}</div>
                                            @if($u->device_fingerprint)
                                                <small class="text-muted" title="{{ $u->device_fingerprint }}">Empreinte : {{ substr($u->device_fingerprint, 0, 8) }}...</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($u->fighter)
                                                <div class="fw-bold text-dark">{{ $u->fighter->name }}</div>
                                                <small class="text-muted">Clan {{ $u->fighter->clan }} • Niv. {{ $u->fighter->level }}</small>
                                            @else
                                                <span class="badge bg-light text-muted">Aucun Shinobi</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($u->has_paid_microtransaction)
                                                <span class="badge bg-success">Pass Illimité (500F)</span>
                                            @else
                                                <span class="badge bg-secondary">Quota 2 Tournois</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($u->is_suspended)
                                                <span class="badge bg-danger">Suspendu</span>
                                            @else
                                                <span class="badge bg-success">Actif</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <!-- Cadeau / Action rapide -->
                                                <a href="{{ route('admin.gifts.index', ['q' => $u->username]) }}" class="btn btn-sm btn-outline-primary" title="Espace Cadeau (XP, Jetons, Pass)">
                                                    <i class="bi bi-gift-fill"></i>
                                                </a>

                                                <!-- Suspension -->
                                                <form action="{{ route('admin.users.toggle-suspension', $u) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $u->is_suspended ? 'btn-outline-success' : 'btn-outline-warning' }}" 
                                                        title="{{ $u->is_suspended ? 'Débloquer le joueur' : 'Suspendre le compte' }}">
                                                        <i class="bi {{ $u->is_suspended ? 'bi-unlock-fill' : 'bi-lock-fill' }}"></i>
                                                    </button>
                                                </form>

                                                <!-- Suppression Fighter -->
                                                @if($u->fighter)
                                                    <form action="{{ route('admin.users.delete-fighter', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Réinitialiser le combattant de {{ $u->username }} ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-info" title="Supprimer son Shinobi (Reset)">
                                                            <i class="bi bi-person-x-fill"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <!-- Suppression User -->
                                                <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement le compte de {{ $u->username }} ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer le joueur">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($users, 'links'))
                        <div class="p-3">
                            {{ $users->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
