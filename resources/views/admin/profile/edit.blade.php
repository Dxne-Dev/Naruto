@extends('layouts.admin')

@section('title', 'Profil Administrateur')
@section('page_title', 'Profil Arbitre & Sécurité')
@section('page_subtitle', 'Identifiants de commandement et mise à jour du mot de passe.')

@section('page_actions')
<a href="{{ route('admin.dashboard') }}" class="btn btn-light d-flex align-items-center gap-1">
    <i class="bi bi-arrow-left"></i>
    <span>Retour Tableau de Bord</span>
</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Paramètres du Compte Arbitre</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <!-- Email (Locked) -->
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Adresse Email (Verrouillée)</label>
                        <input type="email" class="form-control bg-light" value="{{ $user->email }}" disabled readonly style="cursor: not-allowed;">
                        <small class="text-muted">L'adresse email est liée au rôle Game Master et ne peut pas être changée.</small>
                    </div>

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label font-weight-bold">Nom Complet *</label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Username -->
                    <div class="mb-4">
                        <label for="username" class="form-label font-weight-bold">Pseudonyme Unique *</label>
                        <input type="text" id="username" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3">Modifier le Mot de Passe (Optionnel)</h5>

                    <!-- Current Password -->
                    <div class="mb-3">
                        <label for="current_password" class="form-label font-weight-bold">Mot de Passe Actuel</label>
                        <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="••••••••">
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label font-weight-bold">Nouveau Mot de Passe</label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label font-weight-bold">Confirmer le Mot de Passe</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="••••••••">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Sauvegarder les Modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
