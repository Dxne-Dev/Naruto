<?php

namespace App\Services;

use App\Models\Fighter;
use App\Models\IpTournamentQuota;
use App\Models\MatchGame;
use App\Models\Tournament;
use App\Models\TournamentEntry;
use Illuminate\Support\Facades\DB;

class TournamentService
{
    public function __construct(
        protected BracketGenerator $bracketGenerator,
        protected BattleEngine $battleEngine,
        protected XpService $xpService,
    ) {}

    /**
     * Register a fighter into a tournament.
     *
     * @throws \RuntimeException
     */
    public function registerFighter(
        Tournament $tournament,
        Fighter $fighter,
        ?string $registrationIp = null,
        ?string $deviceFingerprint = null,
        ?string $userAgent = null,
    ): TournamentEntry {
        if ($tournament->status !== Tournament::STATUS_REGISTRATION) {
            throw new \RuntimeException('Les inscriptions sont fermées pour ce tournoi.');
        }

        $now = now();
        if ($tournament->registration_opens_at && $now->lt($tournament->registration_opens_at)) {
            throw new \RuntimeException("Les inscriptions n'ouvrent que le {$tournament->registration_opens_at->format('d/m/Y à H:i')}.");
        }

        if ($tournament->registration_closes_at && $now->gt($tournament->registration_closes_at)) {
            throw new \RuntimeException("La date limite d'inscription est dépassée depuis le {$tournament->registration_closes_at->format('d/m/Y à H:i')}.");
        }

        $limit = $tournament->max_participants ?? 8;
        if ($tournament->entries()->count() >= $limit) {
            throw new \RuntimeException("Le tournoi a atteint le nombre maximal de {$limit} participants.");
        }

        if ($tournament->entries()->where('fighter_id', $fighter->id)->exists()) {
            throw new \RuntimeException('Ce combattant est déjà inscrit au tournoi.');
        }

        $fingerprint = $deviceFingerprint ?? ($registrationIp || $userAgent ? IpTournamentQuota::generateFingerprint($registrationIp, $userAgent) : null);

        // Anti-triche Salons Privés : Empêcher deux combattants partageant le même appareil (device fingerprint) d'entrer dans le même salon
        if ($tournament->is_private && $fingerprint) {
            $duplicateDeviceInRoom = $tournament->entries()
                ->where('device_fingerprint', $fingerprint)
                ->where('fighter_id', '!=', $fighter->id)
                ->exists();

            if ($duplicateDeviceInRoom) {
                throw new \RuntimeException('Un combattant issu de cet appareil participe déjà à ce salon privé. Le multicompte est interdit.');
            }
        }

        $entry = TournamentEntry::create([
            'tournament_id' => $tournament->id,
            'fighter_id' => $fighter->id,
            'registered_at' => now(),
            'registration_ip' => $registrationIp,
            'device_fingerprint' => $fingerprint,
        ]);

        // If user has not paid microtransaction, increment persistent IP and device fingerprint usage
        if ($fighter->user && ! $fighter->user->has_paid_microtransaction && ($registrationIp || $userAgent)) {
            IpTournamentQuota::incrementUsage($registrationIp, $userAgent);
        }

        return $entry;
    }

    /**
     * Remove a fighter registration.
     */
    public function unregisterFighter(Tournament $tournament, Fighter $fighter): bool
    {
        if ($tournament->status !== Tournament::STATUS_REGISTRATION) {
            throw new \RuntimeException('Impossible de se désinscrire une fois le tournoi verrouillé.');
        }

        return (bool) $tournament->entries()->where('fighter_id', $fighter->id)->delete();
    }

    /**
     * Lock tournament and generate bracket.
     */
    public function lockAndGenerateBracket(Tournament $tournament): array
    {
        return $this->bracketGenerator->generate($tournament);
    }

    /**
     * Start the tournament.
     */
    public function startTournament(Tournament $tournament): void
    {
        if ($tournament->status !== Tournament::STATUS_LOCKED) {
            $this->lockAndGenerateBracket($tournament);
        }

        $tournament->update([
            'status' => Tournament::STATUS_RUNNING,
            'starts_at' => $tournament->starts_at ?? now(),
        ]);
    }

    /**
     * Resolve a single match with the BattleEngine and distribute XP per match.
     */
    public function resolveMatch(MatchGame $match): MatchGame
    {
        if ($match->status === MatchGame::STATUS_COMPLETED && $match->winner_id) {
            return $match;
        }

        if (! $match->fighter_a_id || ! $match->fighter_b_id) {
            throw new \RuntimeException('Les deux combattants doivent être qualifiés pour lancer le match.');
        }

        $fighterA = Fighter::findOrFail($match->fighter_a_id);
        $fighterB = Fighter::findOrFail($match->fighter_b_id);

        $battle = $this->battleEngine->simulate($fighterA, $fighterB, $match);

        $winnerId = $battle->winner_id;
        $loserId = ($winnerId === $fighterA->id) ? $fighterB->id : $fighterA->id;

        $winner = ($winnerId === $fighterA->id) ? $fighterA : $fighterB;
        $loser = ($winnerId === $fighterA->id) ? $fighterB : $fighterA;

        // User requested XP economy:
        // Pour les tournois officiels uniquement : +150 XP par victoire de match, +50 XP consolation
        // Pour les salons privés : aucune XP intermédiaire de match officiel n'est attribuée, uniquement la récompense forfaitaire finale (20 XP participant, 60 XP gagnant)
        if (! $match->tournament->is_private) {
            // • Victoire de match : +150 XP
            $this->xpService->awardXp(
                $winner,
                XpService::XP_MATCH_WIN,
                "Victoire au Tour {$match->round} du tournoi [{$match->tournament->title}]",
                'match_win',
                $match->id
            );

            // • Défaite au 1er tour (Round 1) : +50 XP (Consolation)
            if ($match->round === 1) {
                $this->xpService->awardXp(
                    $loser,
                    XpService::XP_FIRST_ROUND_LOSS,
                    "Consolation 1er tour du tournoi [{$match->tournament->title}]",
                    'match_loss_round_1',
                    $match->id
                );
            }
        }

        $match->update([
            'winner_id' => $winnerId,
            'status' => MatchGame::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        // Advance tournament bracket
        $this->advanceTournament($match->tournament);

        return $match;
    }

    /**
     * Advance tournament tree based on completed matches.
     */
    public function advanceTournament(Tournament $tournament): void
    {
        DB::transaction(function () use ($tournament) {
            $is4Players = ($tournament->max_participants ?? 8) === 4;

            if ($is4Players) {
                // 4 Players: Round 1 (Demies - 2 matches) -> Seed Round 2 (Finale - 1 match)
                $semiMatches = $tournament->matches()->where('round', 1)->orderBy('match_number')->get();
                if ($semiMatches->count() === 2 && $semiMatches->every(fn ($m) => $m->status === MatchGame::STATUS_COMPLETED)) {
                    $final = $tournament->matches()->where('round', 2)->where('match_number', 1)->first();
                    if ($final && (! $final->fighter_a_id || ! $final->fighter_b_id)) {
                        $final->update([
                            'fighter_a_id' => $semiMatches[0]->winner_id,
                            'fighter_b_id' => $semiMatches[1]->winner_id,
                        ]);
                    }
                }

                // Check Round 2 (Finale) -> Complete tournament
                $finalMatch = $tournament->matches()->where('round', 2)->where('match_number', 1)->first();
                if ($finalMatch && $finalMatch->status === MatchGame::STATUS_COMPLETED && $tournament->status !== Tournament::STATUS_COMPLETED) {
                    $this->finalizeTournament($tournament, $finalMatch);
                }
            } else {
                // 8 Players:
                // Check Round 1 (Quarts) -> Seed Round 2 (Demies)
                $qfMatches = $tournament->matches()->where('round', 1)->orderBy('match_number')->get();
                if ($qfMatches->count() === 4 && $qfMatches->every(fn ($m) => $m->status === MatchGame::STATUS_COMPLETED)) {
                    $semi1 = $tournament->matches()->where('round', 2)->where('match_number', 1)->first();
                    if ($semi1 && (! $semi1->fighter_a_id || ! $semi1->fighter_b_id)) {
                        $semi1->update([
                            'fighter_a_id' => $qfMatches[0]->winner_id,
                            'fighter_b_id' => $qfMatches[1]->winner_id,
                        ]);
                    }

                    $semi2 = $tournament->matches()->where('round', 2)->where('match_number', 2)->first();
                    if ($semi2 && (! $semi2->fighter_a_id || ! $semi2->fighter_b_id)) {
                        $semi2->update([
                            'fighter_a_id' => $qfMatches[2]->winner_id,
                            'fighter_b_id' => $qfMatches[3]->winner_id,
                        ]);
                    }
                }

                // Check Round 2 (Demies) -> Seed Round 3 (Finale)
                $semiMatches = $tournament->matches()->where('round', 2)->orderBy('match_number')->get();
                if ($semiMatches->count() === 2 && $semiMatches->every(fn ($m) => $m->status === MatchGame::STATUS_COMPLETED)) {
                    $final = $tournament->matches()->where('round', 3)->where('match_number', 1)->first();
                    if ($final && (! $final->fighter_a_id || ! $final->fighter_b_id)) {
                        $final->update([
                            'fighter_a_id' => $semiMatches[0]->winner_id,
                            'fighter_b_id' => $semiMatches[1]->winner_id,
                        ]);
                    }
                }

                // Check Round 3 (Finale) -> Complete tournament and award Champion XP
                $finalMatch = $tournament->matches()->where('round', 3)->where('match_number', 1)->first();
                if ($finalMatch && $finalMatch->status === MatchGame::STATUS_COMPLETED && $tournament->status !== Tournament::STATUS_COMPLETED) {
                    $this->finalizeTournament($tournament, $finalMatch);
                }
            }
        });
    }

    /**
     * Finalize tournament results, final positions, and Champion bonus.
     */
    protected function finalizeTournament(Tournament $tournament, MatchGame $finalMatch): void
    {
        $championId = $finalMatch->winner_id;
        $finalistId = ($finalMatch->fighter_a_id === $championId) ? $finalMatch->fighter_b_id : $finalMatch->fighter_a_id;

        $is4Players = ($tournament->max_participants ?? 8) === 4;

        if ($is4Players) {
            $semiMatches = $tournament->matches()->where('round', 1)->get();
            $semiLoserIds = [];
            foreach ($semiMatches as $sm) {
                $loser = ($sm->fighter_a_id === $sm->winner_id) ? $sm->fighter_b_id : $sm->fighter_a_id;
                if ($loser) {
                    $semiLoserIds[] = $loser;
                }
            }

            if ($championId) {
                $tournament->entries()->where('fighter_id', $championId)->update(['final_position' => 1]);
            }
            if ($finalistId) {
                $tournament->entries()->where('fighter_id', $finalistId)->update(['final_position' => 2]);
            }
            foreach ($semiLoserIds as $index => $id) {
                $tournament->entries()->where('fighter_id', $id)->update(['final_position' => 3 + $index]);
            }
        } else {
            $semiMatches = $tournament->matches()->where('round', 2)->get();
            $semiLoserIds = [];
            foreach ($semiMatches as $sm) {
                $loser = ($sm->fighter_a_id === $sm->winner_id) ? $sm->fighter_b_id : $sm->fighter_a_id;
                if ($loser) {
                    $semiLoserIds[] = $loser;
                }
            }

            $qfMatches = $tournament->matches()->where('round', 1)->get();
            $qfLoserIds = [];
            foreach ($qfMatches as $qm) {
                $loser = ($qm->fighter_a_id === $qm->winner_id) ? $qm->fighter_b_id : $qm->fighter_a_id;
                if ($loser) {
                    $qfLoserIds[] = $loser;
                }
            }

            // Set final positions
            if ($championId) {
                $tournament->entries()->where('fighter_id', $championId)->update(['final_position' => 1]);
            }
            if ($finalistId) {
                $tournament->entries()->where('fighter_id', $finalistId)->update(['final_position' => 2]);
            }
            foreach ($semiLoserIds as $index => $id) {
                $tournament->entries()->where('fighter_id', $id)->update(['final_position' => 3 + $index]);
            }
            foreach ($qfLoserIds as $index => $id) {
                $tournament->entries()->where('fighter_id', $id)->update(['final_position' => 5 + $index]);
            }
        }

        $tournament->update(['status' => Tournament::STATUS_COMPLETED]);

        // Distribution des récompenses
        if ($tournament->is_private) {
            // RÉCOMPENSES TOURNOI PRIVÉ ENTRE AMIS :
            // 1. Tous les participants : 20 XP + 3 Jetons
            $allEntries = $tournament->entries()->with('fighter.user')->get();
            foreach ($allEntries as $entry) {
                $fighter = $entry->fighter;
                if ($fighter) {
                    $this->xpService->awardXp(
                        $fighter,
                        20,
                        "Participation au salon privé [{$tournament->title}]",
                        'private_tournament_participation',
                        $tournament->id
                    );
                    if ($fighter->user) {
                        $fighter->user->increment('wheel_tokens', 3);
                    }
                }
            }

            // 2. Vainqueur du salon : +60 XP + 5 Jetons
            if ($championId) {
                $champion = Fighter::find($championId);
                if ($champion) {
                    $this->xpService->awardXp(
                        $champion,
                        60,
                        "Victoire du salon privé [{$tournament->title}]",
                        'private_tournament_champion',
                        $tournament->id
                    );
                    if ($champion->user) {
                        $champion->user->increment('wheel_tokens', 5);
                    }
                }
            }
        } else {
            // Tournoi Officiel Public : Award Champion bonus standard
            if ($championId) {
                $champion = Fighter::find($championId);
                if ($champion) {
                    $this->xpService->awardXp(
                        $champion,
                        XpService::XP_CHAMPION,
                        "Grand Vainqueur du tournoi [{$tournament->title}]",
                        'tournament_champion_grand_prize',
                        $tournament->id
                    );
                }
            }
        }
    }

    /**
     * Resolve all pending ready matches in tournament.
     */
    public function resolveAllPendingMatches(Tournament $tournament): void
    {
        if ($tournament->status !== Tournament::STATUS_RUNNING) {
            $this->startTournament($tournament);
        }

        for ($r = 1; $r <= 3; $r++) {
            $matches = $tournament->matches()->where('round', $r)->get();
            foreach ($matches as $match) {
                if ($match->status !== MatchGame::STATUS_COMPLETED && $match->fighter_a_id && $match->fighter_b_id) {
                    $this->resolveMatch($match);
                }
            }
        }
    }
}
