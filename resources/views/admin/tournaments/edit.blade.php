@extends('layouts.admin')

@section('title', 'Gestion : ' . $tournament->title)
@section('page_title', 'Gestion : ' . $tournament->title)
@section('page_subtitle', 'Arbitrage en temps réel, génération de tableau et simulation des combats.')

@section('page_actions')
<div class="d-flex gap-2">
    <a href="{{ route('tournaments.show', $tournament) }}" class="btn btn-info text-white d-flex align-items-center gap-1" target="_blank">
        <i class="bi bi-eye"></i>
        <span>Vue Joueur</span>
    </a>
    <a href="{{ route('admin.tournaments.index') }}" class="btn btn-light d-flex align-items-center gap-1">
        <i class="bi bi-arrow-left"></i>
        <span>Retour</span>
    </a>
</div>
@endsection

@section('content')
<!-- Actions Rapides Game Master -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Commandes d'Arbitrage Rapide</h4>
                <span class="badge bg-primary text-uppercase">{{ $tournament->status }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- 1. Fill bots (if < 8) -->
                    <div class="col-12 col-md-4">
                        <div class="p-3 border rounded h-100 d-flex flex-column justify-content-between bg-light">
                            <div>
                                <span class="badge bg-secondary mb-2">01 / RECRUTEMENT</span>
                                <h6 class="font-weight-bold mb-1">Inscriptions ({{ $tournament->entries->count() }}/8)</h6>
                                <p class="text-muted small mb-3">Combler les places vacantes avec des ninjas PNJ pour atteindre 8.</p>
                            </div>
                            @if($tournament->entries->count() < 8 && in_array($tournament->status, ['registration', 'open']))
                                <form method="POST" action="{{ route('admin.tournaments.fill-bots', $tournament) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary w-100">
                                        <i class="bi bi-robot me-1"></i> Compléter avec IA ({{ 8 - $tournament->entries->count() }})
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-sm btn-outline-secondary w-100" disabled>Complet (8/8)</button>
                            @endif
                        </div>
                    </div>

                    <!-- 2. Generate Bracket -->
                    <div class="col-12 col-md-4">
                        <div class="p-3 border rounded h-100 d-flex flex-column justify-content-between bg-light">
                            <div>
                                <span class="badge bg-secondary mb-2">02 / ARBORESCENCE</span>
                                <h6 class="font-weight-bold mb-1">Tirage Officiel</h6>
                                <p class="text-muted small mb-3">Générer les quarts de finale et verrouiller l'arbre des combats.</p>
                            </div>
                            @if($tournament->entries->count() === 8 && in_array($tournament->status, ['registration', 'open', 'draft']))
                                <form method="POST" action="{{ route('admin.tournaments.bracket', $tournament) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                        <i class="bi bi-diagram-3 me-1"></i> Générer le Tableau
                                    </button>
                                </form>
                            @elseif($tournament->bracket_generated_at)
                                <span class="badge bg-success w-100 py-2">
                                    <i class="bi bi-check-circle me-1"></i> Tableau Validé
                                </span>
                            @else
                                <button class="btn btn-sm btn-outline-secondary w-100" disabled>Requis : 8 Joueurs</button>
                            @endif
                        </div>
                    </div>

                    <!-- 3. Resolve Matches / Auto Play -->
                    <div class="col-12 col-md-4">
                        <div class="p-3 border rounded h-100 d-flex flex-column justify-content-between bg-light">
                            <div>
                                <span class="badge bg-secondary mb-2">03 / EXÉCUTION</span>
                                <h6 class="font-weight-bold mb-1">Moteur de Combat</h6>
                                <p class="text-muted small mb-3">Simuler tous les duels du tableau et couronner le champion.</p>
                            </div>
                            @if($tournament->status !== 'completed' && $tournament->bracket_generated_at)
                                <form method="POST" action="{{ route('admin.tournaments.resolve-all', $tournament) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger w-100">
                                        <i class="bi bi-lightning-charge me-1"></i> Simulation Totale
                                    </button>
                                </form>
                            @elseif($tournament->status === 'completed')
                                <span class="badge bg-secondary w-100 py-2">
                                    <i class="bi bi-trophy-fill me-1"></i> Session Clôturée
                                </span>
                            @else
                                <button class="btn btn-sm btn-outline-secondary w-100" disabled>Générer l'arbre d'abord</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ligne Participants & Configuration -->
<div class="row">
    <!-- Liste des participants -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Combattants Inscrits ({{ $tournament->entries->count() }}/8)</h4>
                <span class="badge bg-info">{{ $tournament->entries->where('is_bot', false)->count() }} Humains / {{ $tournament->entries->where('is_bot', true)->count() }} Bots</span>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @forelse($tournament->entries as $entry)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-dark rounded-pill">#{{ $entry->seed ?? '?' }}</span>
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $entry->fighter->name }}</h6>
                                    <small class="text-muted">
                                        {{ $entry->fighter->clan }} ({{ $entry->fighter->village }}) • {{ ucfirst($entry->fighter->archetype) }}
                                    </small>
                                </div>
                            </div>
                            <div>
                                @if($entry->is_bot)
                                    <span class="badge bg-soft-warning text-warning">BOT IA</span>
                                @else
                                    <span class="badge bg-soft-success text-success">Joueur : {{ $entry->fighter->user?->username }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            Aucun participant inscrit pour l'instant.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Paramètres de session -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-0">Paramètres de la Session</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.tournaments.update', $tournament) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="title" class="form-label font-weight-bold">Titre du Tournoi</label>
                        <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $tournament->title) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label font-weight-bold">Statut de la Session</label>
                        <select class="form-select" id="status" name="status" required>
                            @foreach(['draft', 'registration', 'open', 'locked', 'in_progress', 'completed', 'cancelled'] as $st)
                                <option value="{{ $st }}" {{ $tournament->status === $st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="registration_opens_at" class="form-label font-weight-bold">Ouverture Inscriptions *</label>
                            <input type="datetime-local" class="form-control @error('registration_opens_at') is-invalid @enderror" id="registration_opens_at" name="registration_opens_at" 
                                value="{{ old('registration_opens_at', $tournament->registration_opens_at ? $tournament->registration_opens_at->format('Y-m-d\TH:i') : '') }}" required>
                            @error('registration_opens_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="registration_closes_at" class="form-label font-weight-bold">Clôture Inscriptions *</label>
                            <input type="datetime-local" class="form-control @error('registration_closes_at') is-invalid @enderror" id="registration_closes_at" name="registration_closes_at" 
                                value="{{ old('registration_closes_at', $tournament->registration_closes_at ? $tournament->registration_closes_at->format('Y-m-d\TH:i') : '') }}" required>
                            @error('registration_closes_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="starts_at" class="form-label font-weight-bold">Début des Duels *</label>
                        <input type="datetime-local" class="form-control @error('starts_at') is-invalid @enderror" id="starts_at" name="starts_at" 
                            value="{{ old('starts_at', $tournament->starts_at ? $tournament->starts_at->format('Y-m-d\TH:i') : '') }}" required>
                        @error('starts_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save me-1"></i> Enregistrer les Modifications
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
