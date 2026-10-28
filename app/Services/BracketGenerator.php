<?php

namespace App\Services;

use App\Models\MatchGame;
use App\Models\Tournament;
use Illuminate\Support\Facades\DB;

class BracketGenerator
{
    /**
     * Generate the bracket for a tournament (supports 4 or 8 players).
     *
     * @return array<MatchGame>
     *
     * @throws \RuntimeException
     */
    public function generate(Tournament $tournament): array
    {
        $entries = $tournament->entries()->with('fighter')->get();
        $count = $entries->count();
        $targetCount = $tournament->max_participants ?? 8;

        if ($count !== 4 && $count !== 8) {
            throw new \RuntimeException("Le tournoi requiert exactement 4 ou 8 combattants pour générer le tableau (actuellement : {$count}).");
        }

        return DB::transaction(function () use ($tournament, $entries, $count) {
            // Delete existing pending matches if any
            $tournament->matches()->delete();

            // Shuffle entries randomly to assign seeds
            $shuffled = $entries->shuffle()->values();

            foreach ($shuffled as $index => $entry) {
                $entry->update(['seed' => $index + 1]);
            }

            $createdMatches = [];

            if ($count === 4) {
                // Bracket à 4 participants:
                // Round 1 (Demi-finales - 2 matches)
                for ($m = 0; $m < 2; $m++) {
                    $fighterA = $shuffled[$m * 2]->fighter_id;
                    $fighterB = $shuffled[($m * 2) + 1]->fighter_id;

                    $match = MatchGame::create([
                        'tournament_id' => $tournament->id,
                        'round' => 1,
                        'match_number' => $m + 1,
                        'fighter_a_id' => $fighterA,
                        'fighter_b_id' => $fighterB,
                        'winner_id' => null,
                        'status' => MatchGame::STATUS_PENDING,
                    ]);

                    $createdMatches[] = $match;
                }

                // Round 2 (Finale - 1 match)
                $finalMatch = MatchGame::create([
                    'tournament_id' => $tournament->id,
                    'round' => 2,
                    'match_number' => 1,
                    'fighter_a_id' => null,
                    'fighter_b_id' => null,
                    'winner_id' => null,
                    'status' => MatchGame::STATUS_PENDING,
                ]);

                $createdMatches[] = $finalMatch;
            } else {
                // Bracket à 8 participants:
                // 1. Create Round 1 (Quarts de finale - 4 matches)
                for ($m = 0; $m < 4; $m++) {
                    $fighterA = $shuffled[$m * 2]->fighter_id;
                    $fighterB = $shuffled[($m * 2) + 1]->fighter_id;

                    $match = MatchGame::create([
                        'tournament_id' => $tournament->id,
                        'round' => 1,
                        'match_number' => $m + 1,
                        'fighter_a_id' => $fighterA,
                        'fighter_b_id' => $fighterB,
                        'winner_id' => null,
                        'status' => MatchGame::STATUS_PENDING,
                    ]);

                    $createdMatches[] = $match;
                }

                // 2. Create Round 2 (Demi-finales - 2 matches)
                for ($m = 1; $m <= 2; $m++) {
                    $match = MatchGame::create([
                        'tournament_id' => $tournament->id,
                        'round' => 2,
                        'match_number' => $m,
                        'fighter_a_id' => null,
                        'fighter_b_id' => null,
                        'winner_id' => null,
                        'status' => MatchGame::STATUS_PENDING,
                    ]);

                    $createdMatches[] = $match;
                }

                // 3. Create Round 3 (Finale - 1 match)
                $finalMatch = MatchGame::create([
                    'tournament_id' => $tournament->id,
                    'round' => 3,
                    'match_number' => 1,
                    'fighter_a_id' => null,
                    'fighter_b_id' => null,
                    'winner_id' => null,
                    'status' => MatchGame::STATUS_PENDING,
                ]);

                $createdMatches[] = $finalMatch;
            }

            $tournament->update([
                'status' => Tournament::STATUS_LOCKED,
                'bracket_generated_at' => now(),
            ]);

            return $createdMatches;
        });
    }
}
