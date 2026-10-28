<?php

namespace App\Console\Commands;

use App\Models\Fighter;
use App\Models\Tournament;
use App\Models\TournamentEntry;
use App\Models\User;
use App\Services\FighterGenerator;
use App\Services\TournamentService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[Signature('tournaments:auto-fill-bots {--lock : Verrouiller et générer le bracket immédiatement après comblement} {--now : Combler immédiatement sans vérifier le délai de clôture}')]
#[Description('Auto-comble les tournois par des bots s\'il reste moins de 30 minutes avant clôture des inscriptions')]
class AutoFillTournamentBotsCommand extends Command
{
    /**
     * Alternative aliases for this command.
     *
     * @var array<int, string>
     */
    protected $aliases = ['tournaments:fill-bots'];

    /**
     * Execute the console command.
     */
    public function handle(FighterGenerator $fighterGenerator, TournamentService $tournamentService): int
    {
        $now = now();
        $isNow = (bool) $this->option('now');
        $threshold = now()->addMinutes(30);

        // Find registration tournaments closing within 30 minutes (or any registration tournament if --now)
        $query = Tournament::where('status', Tournament::STATUS_REGISTRATION);
        if (! $isNow) {
            $query->whereNotNull('registration_closes_at')
                ->where('registration_closes_at', '<=', $threshold);
        }

        $tournaments = $query->get();

        if ($tournaments->isEmpty()) {
            $this->info('Aucun tournoi éligible pour le comblement automatique.');

            return Command::SUCCESS;
        }

        foreach ($tournaments as $tournament) {
            $currentCount = $tournament->entries()->count();
            $needed = 8 - $currentCount;

            if ($needed > 0) {
                $this->info("Tournoi [{$tournament->title}] : {$currentCount}/8. Injection de {$needed} bots PNJ...");

                // Liste des clans disponibles mélangée pour garantir une diversité de clans sans doublons excessifs
                $availableClans = array_keys(FighterGenerator::CLANS);
                shuffle($availableClans);

                // Noms déjà présents dans le tournoi pour interdire tout doublon homonyme
                $existingNames = $tournament->entries()->with('fighter')->get()->pluck('fighter.name')->filter()->toArray();

                for ($i = 1; $i <= $needed; $i++) {
                    $botUsername = 'Bot_'.rand(100, 999).'_'.substr(md5(uniqid()), 0, 4);
                    $botUser = User::create([
                        'username' => $botUsername,
                        'name' => 'IA '.$botUsername,
                        'email' => $botUsername.'@arena.local',
                        'password' => Hash::make('secret123'),
                        'role' => User::ROLE_PLAYER,
                        'has_paid_microtransaction' => true,
                    ]);

                    // Sélection d'un clan uniforme équilibré
                    $assignedClan = $availableClans[($i - 1) % count($availableClans)];

                    // Générer un candidat unique non présent dans la session
                    $attempts = 0;
                    do {
                        $candidate = $fighterGenerator->generateCandidate($assignedClan);
                        $attempts++;
                    } while (in_array($candidate['name_suggestion'], $existingNames) && $attempts < 10);

                    $existingNames[] = $candidate['name_suggestion'];

                    $botFighter = Fighter::create([
                        'user_id' => $botUser->id,
                        'name' => $candidate['name_suggestion'],
                        'archetype' => $candidate['specialization'],
                        'clan' => $candidate['clan'],
                        'village' => $candidate['village'],
                        'ninja_rank' => 'Genin',
                        'primary_element' => $candidate['primary_element'],
                        'specialization' => $candidate['specialization'],
                        'stat_nin' => $candidate['stats']['stat_nin'],
                        'stat_tai' => $candidate['stats']['stat_tai'],
                        'stat_gen' => $candidate['stats']['stat_gen'],
                        'stat_int' => $candidate['stats']['stat_int'],
                        'stat_str' => $candidate['stats']['stat_str'],
                        'stat_spd' => $candidate['stats']['stat_spd'],
                        'stat_end' => $candidate['stats']['stat_end'],
                        'stat_sea' => $candidate['stats']['stat_sea'],
                        'power' => (int) round($candidate['stats']['stat_str'] * 4),
                        'speed' => (int) round($candidate['stats']['stat_spd'] * 4),
                        'defense' => (int) round($candidate['stats']['stat_end'] * 4),
                        'control' => (int) round($candidate['stats']['stat_nin'] * 4),
                        'special_power' => $candidate['primary_element'].' Jutsu',
                        'talent' => $candidate['passive_description'],
                        'portrait_path' => $candidate['portrait'],
                        'level' => 1,
                        'xp' => 0,
                        'is_bot' => true,
                    ]);

                    if (! empty($candidate['jutsu_ids'])) {
                        $botFighter->jutsus()->sync($candidate['jutsu_ids']);
                    }

                    TournamentEntry::create([
                        'tournament_id' => $tournament->id,
                        'fighter_id' => $botFighter->id,
                        'registered_at' => now(),
                        'registration_ip' => '127.0.0.1',
                        'device_fingerprint' => 'bot_'.$botFighter->id,
                    ]);
                }

                $this->info("Tournoi [{$tournament->title}] comblé avec succès à 8/8 participants.");
            }

            // If tournament reached full capacity and is at/past closing time, lock and build official bracket
            if ($tournament->entries()->count() >= 8 && ($tournament->registration_closes_at <= now() || $this->option('lock'))) {
                $tournamentService->lockAndGenerateBracket($tournament);
                $this->info("Tournoi [{$tournament->title}] verrouillé et tableau de combat officiel généré.");
            }
        }

        return Command::SUCCESS;
    }
}
