@extends('layouts.app')

@section('title', 'Panthéon des Champions - Hall of Fame')

@section('content')
<div class="container py-4" style="max-width: 1440px;">
    <!-- Hero Header -->
    <div class="text-center mb-5">
        <span class="mono-eyebrow text-danger">ARCHIVES OFFICIELLES &bull; HISTOIRE DE L'ARÈNE</span>
        <h1 class="heading-hero mb-2">
            Panthéon des Champions
        </h1>
        <p class="text-muted small mx-auto font-mono" style="max-width: 640px;">
            Les légendes de l'arène immortalisées. Retrouve le palmarès officiel de tous les examens Chūnin, leurs grands vainqueurs et les archives des duels décisifs.
        </p>
    </div>

    <!-- Champions Leaderboard (Les plus titrés) -->
    <div class="card-monad mb-5 p-4 p-md-5">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <span class="mono-eyebrow">TABLEAU D'HONNEUR DES NINJAS</span>
                <h3 class="serif-heading mb-0" style="font-size: 1.8rem;">Combattants les Plus Titrés</h3>
            </div>
            <span class="tag-monad tag-monad-red font-mono" style="font-size: 11px;">TITRES OFFICIELS</span>
        </div>

        @if($championsLeaderboard->isEmpty())
            <div class="text-center py-5 text-muted font-mono">
                <p class="small mb-0">Aucun shinobi n'a encore été sacré Champion dans l'arène.</p>
                <span class="mono-eyebrow mt-1">SOIS LE PREMIER À REMPORTER UN EXAMEN CHŪNIN !</span>
            </div>
        @else
            <div class="row g-3">
                @foreach($championsLeaderboard as $index => $champ)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="p-3 bg-white h-100 d-flex flex-column justify-content-between" style="border-radius: 18px; border: 1px solid {{ $index === 0 ? 'var(--color-brand-red)' : 'var(--color-ash)' }};">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="tag-monad {{ $index === 0 ? 'tag-monad-red' : '' }} d-inline-flex align-items-center gap-1" style="font-size: 10px;">
                                        @if($index === 0)
                                            <i class="bi bi-crown-fill text-warning"></i> <span>RANG #1</span>
                                        @else
                                            <span>RANG #{{ $index + 1 }}</span>
                                        @endif
                                    </span>
                                    <span class="fw-bold font-mono text-danger" style="font-size: 13px;">
                                        {{ $champ->titles_count }} TITRE{{ $champ->titles_count > 1 ? 'S' : '' }}
                                    </span>
                                </div>
                                <h4 class="serif-heading mb-1" style="font-size: 1.4rem;">{{ $champ->name }}</h4>
                                <div class="text-muted small font-mono" style="font-size: 11px;">
                                    Clan {{ $champ->clan }} &bull; {{ $champ->village }} &bull; N.{{ $champ->level }}
                                </div>
                                <div class="mono-eyebrow m-0 mt-2" style="font-size: 10px; color: var(--color-smoke);">
                                    MAÎTRE : {{ $champ->user?->username ?? 'IA PNJ' }}
                                </div>
                            </div>

                            <a href="{{ route('fighters.show', $champ) }}" class="btn-ghost-monad w-100 mt-3 py-1" style="font-size: 11px;">
                                VOIR LA FICHE NINJA ▸
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Chronological Championships Archive -->
    <div class="card-monad p-4 p-md-5">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <span class="mono-eyebrow">CHRONOLOGIE COMPLÈTE</span>
                <h3 class="serif-heading mb-0" style="font-size: 1.8rem;">Archives des Tournois Clôturés</h3>
            </div>
            <span class="tag-monad font-mono" style="font-size: 11px;">ARCHIVES OFFICIELLES</span>
        </div>

        @if($recentChampionships->isEmpty())
            <div class="text-center py-5 text-muted font-mono">
                <p class="small mb-0">Aucun tournoi clôturé pour l'instant.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach($recentChampionships as $t)
                    @php
                        $championEntry = $t->entries->firstWhere('final_position', 1);
                        $finalMatch = $t->matches->firstWhere('round', 3);
                    @endphp
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="p-4 bg-white h-100 d-flex flex-column justify-content-between" style="border-radius: 20px; border: 1px solid var(--color-ash);">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="tag-monad tag-monad-red" style="font-size: 9px;">SESSION #{{ $t->id }}</span>
                                    <span class="text-muted small font-mono" style="font-size: 11px;">{{ $t->updated_at->format('d/m/Y') }}</span>
                                </div>
                                <h4 class="serif-heading mb-3" style="font-size: 1.5rem;">{{ $t->title }}</h4>

                                @if($championEntry && $championEntry->fighter)
                                    <div class="p-3 mb-3 bg-light rounded-3" style="border-left: 4px solid var(--color-brand-red);">
                                        <span class="mono-eyebrow m-0 text-danger" style="font-size: 9px;">CHAMPION DU TOURNOI :</span>
                                        <div class="fw-bold text-dark font-mono mt-1" style="font-size: 14px;">
                                            {{ $championEntry->fighter->name }}
                                        </div>
                                        <div class="text-muted font-mono" style="font-size: 10px;">
                                            Clan {{ $championEntry->fighter->clan }} &bull; {{ $championEntry->fighter->village }}
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex flex-column gap-2 mt-3">
                                @if($finalMatch && $finalMatch->battle)
                                    <a href="{{ route('battles.show', $finalMatch->battle) }}" class="btn-lake-blue w-100 py-2 text-center" style="font-size: 11px;">
                                        VISIONNER LA FINALE ▸
                                    </a>
                                @endif
                                <a href="{{ route('tournaments.show', $t) }}" class="btn-ghost-monad w-100 py-1 text-center" style="font-size: 11px;">
                                    CONSULTER LE TABLEAU ▸
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $recentChampionships->links() }}
            </div>
        @endif
    </div>
@endsection
