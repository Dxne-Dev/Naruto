@extends('layouts.admin')

@section('title', 'Annuaire des Joueurs')
@section('page_title', 'Annuaire des Joueurs & Combattants')
@section('page_subtitle', 'Gestion des joueurs humains, bots de test, suspension des comptes et modération.')

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
            
            <!-- EN-TÊTE : ONGLETS & ACTIONS RAPIDES -->
            <div class="card-header border-bottom pb-0">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                    
                    <!-- Onglets de filtrage (Réels / Bots / Tous) -->
                    <ul class="nav nav-pills card-header-pills">
                        <li class="nav-item">
                            <a class="nav-link {{ $type === 'real' ? 'active' : '' }} d-flex align-items-center gap-2" 
                               href="{{ route('admin.users.index', ['type' => 'real', 'q' => $search]) }}">
                                <i class="bi bi-person-check-fill"></i>
                                <span>Joueurs Réels</span>
                                <span class="badge {{ $type === 'real' ? 'bg-white text-primary' : 'bg-primary' }}">{{ $realCount }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $type === 'bots' ? 'active' : '' }} d-flex align-items-center gap-2" 
                               href="{{ route('admin.users.index', ['type' => 'bots', 'q' => $search]) }}">
                                <i class="bi bi-robot"></i>
                                <span>Bots IA</span>
                                <span class="badge {{ $type === 'bots' ? 'bg-white text-primary' : 'bg-warning text-dark' }}">{{ $botsCount }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $type === 'all' ? 'active' : '' }} d-flex align-items-center gap-2" 
                               href="{{ route('admin.users.index', ['type' => 'all', 'q' => $search]) }}">
                                <i class="bi bi-people-fill"></i>
                                <span>Tous les Comptes</span>
                                <span class="badge {{ $type === 'all' ? 'bg-white text-primary' : 'bg-secondary' }}">{{ $totalCount }}</span>
                            </a>
                        </li>
                    </ul>

                    <!-- Action de purge si des bots existent -->
                    @if($botsCount > 0)
                        <form action="{{ route('admin.users.purge-bots') }}" method="POST" onsubmit="return confirm('Attention : cette action va supprimer définitivement les {{ $botsCount }} bots IA de la base de données. Confirmer la purge ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1.5" title="Supprimer tous les bots de test pour alléger la base">
                                <i class="bi bi-trash3-fill"></i>
                                <span>Purger tous les Bots ({{ $botsCount }})</span>
                            </button>
                        </form>
                    @endif

                </div>

                <!-- BARRE DE RECHERCHE -->
                <div class="row align-items-center py-2">
                    <div class="col-md-6 col-lg-5">
                        <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex gap-2">
                            <input type="hidden" name="type" value="{{ $type }}">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="text" name="q" class="form-control" placeholder="Rechercher par pseudo, email, shinobi, clan..." value="{{ $search }}">
                                @if($search !== '')
                                    <a href="{{ route('admin.users.index', ['type' => $type]) }}" class="btn btn-outline-secondary" title="Effacer la recherche">
                                        <i class="bi bi-x-lg"></i>
                                    </a>
                                @endif
                                <button class="btn btn-primary" type="submit">Filtrer</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6 col-lg-7 text-md-end text-muted small mt-2 mt-md-0">
                        @if($search !== '')
                            Résultats pour la recherche « <strong class="text-dark">{{ $search }}</strong> » : <strong>{{ $users->total() }}</strong> trouvé(s).
                        @else
                            Affichage : <strong>{{ $users->total() }}</strong> compte(s) au total.
                        @endif
                    </div>
                </div>

            </div>

            <!-- TABLEAU DES UTILISATEURS -->
            <div class="card-body px-0 pt-0">
                @if($users->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                        @if($search !== '')
                            Aucun compte ne correspond à votre recherche « <strong>{{ $search }}</strong> ».
                            <div class="mt-2">
                                <a href="{{ route('admin.users.index', ['type' => $type]) }}" class="btn btn-sm btn-outline-primary">Réinitialiser le filtre</a>
                            </div>
                        @elseif($type === 'bots')
                            Aucun bot injecté dans la base de données.
                        @else
                            Aucun joueur enregistré pour le moment.
                        @endif
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>JOUEUR (PILOTE)</th>
                                    <th>TYPE</th>
                                    <th>EMAIL</th>
                                    <th>IP / EMPREINTE</th>
                                    <th>SHINOBI ASSOCIÉ</th>
                                    <th>STATUT PASS</th>
                                    <th>ÉTAT COMPTE</th>
                                    <th class="text-end">ACTIONS & SANCTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $u)
                                    @php
                                        $isBot = $u->isBot();
                                    @endphp
                                    <tr class="{{ $isBot ? 'opacity-75' : '' }}">
                                        <td>
                                            <div class="fw-bold text-dark">{{ $u->username }}</div>
                                            <small class="text-muted">{{ $u->name }}</small>
                                        </td>
                                        <td>
                                            @if($isBot)
                                                <span class="badge bg-warning text-dark"><i class="bi bi-robot me-1"></i>Bot IA</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-person-fill me-1"></i>Humain</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="font-monospace text-muted">{{ $u->email }}</small>
                                        </td>
                                        <td>
                                            <div class="small font-monospace">{{ $u->registration_ip ?? '127.0.0.1' }}</div>
                                            @if($u->device_fingerprint)
                                                <small class="text-muted font-monospace" title="{{ $u->device_fingerprint }}">Empreinte : {{ substr($u->device_fingerprint, 0, 8) }}...</small>
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
                                                <!-- Cadeau / Action rapide (seulement joueurs humains) -->
                                                @if(! $isBot)
                                                    <a href="{{ route('admin.gifts.index', ['q' => $u->username]) }}" class="btn btn-sm btn-outline-primary" title="Espace Cadeau (XP, Jetons, Pass)">
                                                        <i class="bi bi-gift-fill"></i>
                                                    </a>
                                                @endif

                                                <!-- Suspension -->
                                                <form action="{{ route('admin.users.toggle-suspension', $u) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $u->is_suspended ? 'btn-outline-success' : 'btn-outline-warning' }}" 
                                                        title="{{ $u->is_suspended ? 'Débloquer le compte' : 'Suspendre le compte' }}">
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
                        <div class="p-3 border-top">
                            {{ $users->links() }}
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
