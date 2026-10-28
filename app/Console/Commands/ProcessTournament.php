<?php

namespace App\Console\Commands;

use App\Models\Tournament;
use App\Services\TournamentService;
use Illuminate\Console\Command;

class ProcessTournament extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournament:process {tournament? : ID du tournoi spécifique à traiter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Résout automatiquement les matchs prêts des tournois en cours';

    /**
     * Execute the console command.
     */
    public function handle(TournamentService $tournamentService): int
    {
        $tournamentId = $this->argument('tournament');

        if ($tournamentId) {
            $tournament = Tournament::find($tournamentId);
            if (! $tournament) {
                $this->error("Tournoi #{$tournamentId} introuvable.");

                return self::FAILURE;
            }
            $tournaments = collect([$tournament]);
        } else {
            $now = now();
            $tournaments = Tournament::whereIn('status', [Tournament::STATUS_RUNNING, Tournament::STATUS_LOCKED])
                ->where(function ($query) use ($now) {
                    $query->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', $now);
                })
                ->get();
        }

        if ($tournaments->isEmpty()) {
            $this->info('Aucun tournoi actif à traiter.');

            return self::SUCCESS;
        }

        foreach ($tournaments as $tournament) {
            $this->info("Traitement du tournoi: #{$tournament->id} - {$tournament->title}");
            $tournamentService->resolveAllPendingMatches($tournament);
            $this->info("Tournoi #{$tournament->id} mis à jour (Statut: {$tournament->status}).");
        }

        return self::SUCCESS;
    }
}
