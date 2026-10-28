<?php

namespace App\Http\Controllers;

use App\Models\Fighter;
use App\Models\MatchGame;
use App\Services\FighterGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    /**
     * Display the Hall of Fame & rankings.
     */
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all');
        $selectedClan = $request->query('clan');
        $selectedVillage = $request->query('village');

        $clans = array_keys(FighterGenerator::CLANS);
        $villages = FighterGenerator::VILLAGES;

        // Fenêtre hebdomadaire de 5 jours : Lundi 00:00:00 au Vendredi 23:59:59
        $startOfWeek = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $endOfWeek = (clone $startOfWeek)->addDays(4)->endOfDay();
        $statusCompleted = MatchGame::STATUS_COMPLETED;
        $isLastDay = now()->isFriday();

        // Requête de base avec calcul des victoires/défaites et taux de tournois Lun-Ven
        $baseQuery = Fighter::query()
            ->with(['user'])
            ->select('fighters.*')
            ->selectRaw('
                (SELECT COUNT(*) FROM matches 
                 WHERE matches.winner_id = fighters.id 
                 AND matches.status = ? 
                 AND matches.created_at BETWEEN ? AND ?) as weekly_tourn_wins',
                [$statusCompleted, $startOfWeek, $endOfWeek]
            )
            ->selectRaw('
                (SELECT COUNT(*) FROM matches 
                 WHERE (matches.fighter_a_id = fighters.id OR matches.fighter_b_id = fighters.id)
                 AND matches.winner_id != fighters.id
                 AND matches.status = ? 
                 AND matches.created_at BETWEEN ? AND ?) as weekly_tourn_losses',
                [$statusCompleted, $startOfWeek, $endOfWeek]
            )
            ->selectRaw('
                (SELECT COUNT(*) FROM matches 
                 WHERE (matches.fighter_a_id = fighters.id OR matches.fighter_b_id = fighters.id)
                 AND matches.status = ? 
                 AND matches.created_at BETWEEN ? AND ?) as weekly_tourn_total',
                [$statusCompleted, $startOfWeek, $endOfWeek]
            )
            ->selectRaw('
                (CASE 
                    WHEN (SELECT COUNT(*) FROM matches WHERE (matches.fighter_a_id = fighters.id OR matches.fighter_b_id = fighters.id) AND matches.status = ? AND matches.created_at BETWEEN ? AND ?) > 0
                    THEN (
                        (SELECT COUNT(*) FROM matches WHERE matches.winner_id = fighters.id AND matches.status = ? AND matches.created_at BETWEEN ? AND ?) * 100.0
                    ) / (
                        (SELECT COUNT(*) FROM matches WHERE (matches.fighter_a_id = fighters.id OR matches.fighter_b_id = fighters.id) AND matches.status = ? AND matches.created_at BETWEEN ? AND ?)
                    )
                    ELSE 0 
                END) as weekly_win_rate',
                [
                    $statusCompleted, $startOfWeek, $endOfWeek,
                    $statusCompleted, $startOfWeek, $endOfWeek,
                    $statusCompleted, $startOfWeek, $endOfWeek,
                ]
            )
            ->withCount(['tournamentEntries as tournaments_won_count' => function ($q) {
                $q->where('final_position', 1);
            }])
            ->withCount(['tournamentEntries as tournaments_played_count']);

        // Application des filtres dynamiques (Clan & Village)
        if ($selectedClan) {
            $baseQuery->where('clan', $selectedClan);
        }

        if ($selectedVillage) {
            $baseQuery->where('village', $selectedVillage);
        }

        // Tri du classement : d'abord le taux de victoire Lun-Ven, puis le nombre de victoires, puis les tournois gagnés, puis le niveau et l'XP
        $sortedQuery = (clone $baseQuery)
            ->orderByDesc('weekly_win_rate')
            ->orderByDesc('weekly_tourn_wins')
            ->orderByDesc('tournaments_won_count')
            ->orderByDesc('level')
            ->orderByDesc('xp');

        // Récupération des qualifiés pour les 3 socles (Or, Argent, Bronze) :
        // Règle : on peut être dans le tableau sans être sur les socles.
        // Par défaut personne n'y est tant qu'aucun tournoi n'a été disputé et gagné dans le cycle (weekly_tourn_wins > 0).
        $podiumFighters = (clone $sortedQuery)
            ->whereRaw(
                '(SELECT COUNT(*) FROM matches WHERE matches.winner_id = fighters.id AND matches.status = ? AND matches.created_at BETWEEN ? AND ?) > 0',
                [$statusCompleted, $startOfWeek, $endOfWeek]
            )
            ->take(3)
            ->get();

        $top1 = $podiumFighters->get(0);
        $top2 = $podiumFighters->get(1);
        $top3 = $podiumFighters->get(2);

        // Liste paginée pour le tableau de classement
        $fighters = (clone $sortedQuery)->paginate(15)->withQueryString();

        // Hall of Fame : Vrais joueurs uniquement (user_id IS NOT NULL)
        // Règles d'intronisation officielles :
        // 1. Avoir remporté au moins 1 grand Tournoi Chūnin (final_position = 1)
        // 2. Avoir atteint au minimum le rang de Jōnin (Niveau >= 10)
        $hallOfFameLegends = Fighter::query()
            ->whereNotNull('user_id')
            ->withCount(['tournamentEntries as tournaments_won_count' => function ($q) {
                $q->where('final_position', 1);
            }])
            ->whereHas('tournamentEntries', function ($q) {
                $q->where('final_position', 1);
            })
            ->where('level', '>=', 10)
            ->with(['user'])
            ->orderByDesc('tournaments_won_count')
            ->orderByDesc('level')
            ->orderByDesc('xp')
            ->get();

        return view('leaderboard.index', [
            'fighters' => $fighters,
            'top1' => $top1,
            'top2' => $top2,
            'top3' => $top3,
            'hallOfFameLegends' => $hallOfFameLegends,
            'filter' => $filter,
            'selectedClan' => $selectedClan,
            'selectedVillage' => $selectedVillage,
            'clans' => $clans,
            'villages' => $villages,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
            'isLastDay' => $isLastDay,
        ]);
    }
}
