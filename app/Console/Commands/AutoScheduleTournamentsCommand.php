<?php

namespace App\Console\Commands;

use App\Models\Tournament;
use App\Services\TournamentService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tournaments:auto-schedule {--force : Forcer la création même si ce n\'est pas un jour de cycle (Lun, Mer, Ven)}') ]
#[Description('Gère le cycle récurrent des tournois Chūnin (Lundi, Mercredi, Vendredi)')]
class AutoScheduleTournamentsCommand extends Command
{
    /**
     * Jours programmés pour les tournois (1 = Lundi, 3 = Mercredi, 5 = Vendredi).
     *
     * @var array<int, string>
     */
    protected array $scheduledDays = [
        1 => 'Lundi',
        3 => 'Mercredi',
        5 => 'Vendredi',
    ];

    /**
     * Execute the console command.
     */
    public function handle(TournamentService $tournamentService): int
    {
        $now = now();
        $dayOfWeek = (int) $now->dayOfWeekIso; // 1 (Mon) -> 7 (Sun)
        $isForce = (bool) $this->option('force');

        $this->info("Exécution du planificateur de tournois [{$now->format('Y-m-d H:i:s')} - Jour ISO: {$dayOfWeek}]");

        if (! $isForce && ! array_key_exists($dayOfWeek, $this->scheduledDays)) {
            $this->comment("Aujourd'hui n'est pas un jour officiel de tournoi (Lundi, Mercredi, Vendredi). Utilisez --force pour contourner.");

            return Command::SUCCESS;
        }

        $dayName = $this->scheduledDays[$dayOfWeek] ?? 'Édition Spéciale';

        // 1. Vérifier si un tournoi actif (registration, locked, running) existe déjà aujourd'hui
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();

        $existingToday = Tournament::where(function ($query) use ($todayStart, $todayEnd) {
            $query->whereBetween('created_at', [$todayStart, $todayEnd])
                ->orWhereBetween('registration_opens_at', [$todayStart, $todayEnd]);
        })->whereIn('status', [
            Tournament::STATUS_REGISTRATION,
            Tournament::STATUS_LOCKED,
            Tournament::STATUS_RUNNING,
            Tournament::STATUS_COMPLETED,
        ])->first();

        if (! $existingToday) {
            // Créer le tournoi du jour
            // Inscriptions : de 08h00 à 20h00
            // Début des combats : 20h05
            $opensAt = $now->copy()->setTime(8, 0, 0);
            $closesAt = $now->copy()->setTime(20, 0, 0);
            $startsAt = $now->copy()->setTime(20, 5, 0);

            // Si forcé en fin de journée ou après 20h, ajuster les heures pour que le tournoi soit immédiatement testable/ouvert
            if ($now->gt($closesAt)) {
                $opensAt = $now->copy()->subMinutes(15);
                $closesAt = $now->copy()->addMinutes(10);
                $startsAt = $now->copy()->addMinutes(15);
            }

            $sessionTitle = 'Examen Chūnin — Session du '.$dayName.' '.$now->format('d/m');

            $tournament = Tournament::create([
                'title' => $sessionTitle,
                'status' => Tournament::STATUS_REGISTRATION,
                'registration_opens_at' => $opensAt,
                'registration_closes_at' => $closesAt,
                'starts_at' => $startsAt,
            ]);

            $this->info("Nouveau tournoi créé avec succès : [#{$tournament->id}] {$tournament->title}");
            $this->line(" - Inscriptions ouvertes jusqu'à : {$closesAt->format('H:i')}");
            $this->line(" - Début des combats prévu à : {$startsAt->format('H:i')}");

            $existingToday = $tournament;
        } else {
            $this->line("Tournoi existant pour aujourd'hui : [#{$existingToday->id}] {$existingToday->title} (Statut: {$existingToday->status})");
        }

        // 2. Gestion de l'état du tournoi selon l'heure
        if ($existingToday->status === Tournament::STATUS_REGISTRATION) {
            $closesAt = $existingToday->registration_closes_at ?? $now->copy()->addMinutes(30);

            // Règle des Bots : si nous sommes à moins de 30 minutes de la clôture (ou clôture dépassée) et participants < 8
            if ($now->gte($closesAt->copy()->subMinutes(30))) {
                $entriesCount = $existingToday->entries()->count();
                if ($entriesCount < 8) {
                    $needed = 8 - $entriesCount;
                    $this->warn("Moins de 30 min avant clôture ({$entriesCount}/8 inscrits). Injection de {$needed} shinobi(s) PNJ...");
                    $this->call('tournaments:auto-fill-bots', ['--now' => true]);
                }
            }

            // Clôture des inscriptions et génération de l'arbre
            if ($now->gte($closesAt)) {
                $this->info("Heure de clôture atteinte. Verrouillage du tournoi et génération de l'arbre éliminatoire...");
                // S'assurer qu'il y a 8 combattants
                if ($existingToday->entries()->count() < 8) {
                    $this->call('tournaments:auto-fill-bots', ['--now' => true]);
                }

                try {
                    $tournamentService->lockAndGenerateBracket($existingToday->fresh());
                    $this->info('Tableau de 8 shinobis généré ! Statut passé à LOCKED.');
                } catch (\Throwable $e) {
                    $this->error("Erreur lors de la génération du tableau : {$e->getMessage()}");
                }
            }
        }

        // 3. Si le tournoi est LOCKED et que l'heure de début des combats est atteinte
        if ($existingToday->fresh()->status === Tournament::STATUS_LOCKED) {
            $startsAt = $existingToday->starts_at ?? $now;
            if ($now->gte($startsAt)) {
                $this->info("Heure de début des combats atteinte ({$startsAt->format('H:i')}). Lancement des combats...");
                try {
                    $tournamentService->startTournament($existingToday->fresh());
                    $tournamentService->resolveAllPendingMatches($existingToday->fresh());
                    $this->info('Combats du tournoi résolus avec succès !');
                } catch (\Throwable $e) {
                    $this->error("Erreur lors du traitement des combats : {$e->getMessage()}");
                }
            }
        }

        return Command::SUCCESS;
    }
}
