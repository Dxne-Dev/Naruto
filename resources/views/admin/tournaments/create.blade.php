@extends('layouts.admin')

@section('title', 'Créer un Tournoi')
@section('page_title', 'Nouveau Tournoi Chūnin')
@section('page_subtitle', 'Définis les dates d\'ouverture, de clôture et lance la session de tournoi.')

@section('page_actions')
<a href="{{ route('admin.tournaments.index') }}" class="btn btn-light d-flex align-items-center gap-1">
    <i class="bi bi-arrow-left"></i>
    <span>Retour aux Tournois</span>
</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Paramètres du Tournoi</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.tournaments.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="title" class="form-label font-weight-bold">Titre du Tournoi *</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                            id="title" name="title" value="{{ old('title') }}" required placeholder="Ex: Tournoi des Jonins #1">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label font-weight-bold">Statut Initial *</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="registration" selected>Inscriptions ouvertes (registration)</option>
                            <option value="draft">Brouillon (draft)</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="registration_opens_at" class="form-label font-weight-bold">Ouverture des Inscriptions *</label>
                            <input type="datetime-local" class="form-control @error('registration_opens_at') is-invalid @enderror" 
                                id="registration_opens_at" name="registration_opens_at" value="{{ old('registration_opens_at', now()->format('Y-m-d\TH:i')) }}" required>
                            @error('registration_opens_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="registration_closes_at" class="form-label font-weight-bold">Clôture des Inscriptions *</label>
                            <input type="datetime-local" class="form-control @error('registration_closes_at') is-invalid @enderror" 
                                id="registration_closes_at" name="registration_closes_at" value="{{ old('registration_closes_at', now()->addDays(1)->format('Y-m-d\TH:i')) }}" required>
                            @error('registration_closes_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="starts_at" class="form-label font-weight-bold">Début des Combats (Duels) *</label>
                        <input type="datetime-local" class="form-control @error('starts_at') is-invalid @enderror" 
                            id="starts_at" name="starts_at" value="{{ old('starts_at', now()->addDays(1)->addHours(1)->format('Y-m-d\TH:i')) }}" required>
                        @error('starts_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-soft-primary mb-4">
                        <i class="bi bi-info-circle me-1"></i>
                        Le système injectera automatiquement les bots PNJ à T-5 minutes de la clôture si le tournoi n'est pas rempli à 8 combattants.
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.tournaments.index') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Créer et Programmer le Tournoi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
