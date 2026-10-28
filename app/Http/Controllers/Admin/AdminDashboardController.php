<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fighter;
use App\Models\Tournament;
use App\Models\TournamentEntry;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Show Game Master Admin Dashboard.
     */
    public function index(): View
    {
        $activeTournamentsCount = Tournament::whereIn('status', [
            Tournament::STATUS_REGISTRATION,
            Tournament::STATUS_LOCKED,
            Tournament::STATUS_RUNNING,
        ])->count();

        $realParticipantsCount = TournamentEntry::whereHas('fighter', function ($query) {
            $query->where('is_bot', false);
        })->count();

        $botParticipantsCount = TournamentEntry::whereHas('fighter', function ($query) {
            $query->where('is_bot', true);
        })->count();

        $totalUsersCount = User::where('role', User::ROLE_PLAYER)->count();
        $totalFightersCount = Fighter::where('is_bot', false)->count();

        $recentTournaments = Tournament::withCount('entries')
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard', [
            'activeTournamentsCount' => $activeTournamentsCount,
            'realParticipantsCount' => $realParticipantsCount,
            'botParticipantsCount' => $botParticipantsCount,
            'totalUsersCount' => $totalUsersCount,
            'totalFightersCount' => $totalFightersCount,
            'recentTournaments' => $recentTournaments,
        ]);
    }
}
