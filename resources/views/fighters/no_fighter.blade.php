@extends('layouts.app')

@section('content')
<div class="container py-5 text-center" style="max-width: 600px;">
    <div class="card-monad p-5">
        <span class="mono-eyebrow">REGISTRE INCOMPLET</span>
        <h2 class="serif-heading mt-1 mb-3" style="font-size: 2.2rem;">Aucun Combattant Assigné</h2>
        <p class="text-muted small mb-4">
            Pour intégrer les tournois communautaires et défier les autres joueurs, initialise ton premier profil combattant à travers le rituel d'invocation d'arène.
        </p>
        <a href="{{ route('fighters.create') }}" class="btn-lake-blue">
            GÉNÉRER UN COMBATTANT ▸
        </a>
    </div>
</div>
@endsection
