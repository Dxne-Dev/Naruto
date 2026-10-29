<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\PassRequestAdminController;
use App\Http\Controllers\Admin\PortraitAdminController;
use App\Http\Controllers\Admin\TournamentAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BattleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FighterController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PrivateTournamentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StrategyController;
use App\Http\Controllers\TournamentController;
use Illuminate\Support\Facades\Route;

// Public & Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');

// Guest Auth Routes (Protection brute-force et multi-inscriptions)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware(app()->environment('local') ? [] : ['throttle:10,1440'])
        ->name('register.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Onboarding (Step-by-step Shinobi forging with wheels & microtransaction)
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    // Player Hub
    Route::get('/mon-personnage', [FighterController::class, 'index'])->name('fighters.my-character');
    Route::get('/tournois', [TournamentController::class, 'index'])->name('tournaments.open-list');
    Route::get('/strategies', [StrategyController::class, 'index'])->name('strategies.index');
    Route::post('/strategies', [StrategyController::class, 'update'])->name('strategies.update');
    Route::get('/combats', [BattleController::class, 'index'])->name('battles.index');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Friends & Friendly Duels
    Route::get('/amis', [FriendController::class, 'index'])->name('friends.index');
    Route::post('/amis/demande', [FriendController::class, 'sendRequest'])->name('friends.request');
    Route::post('/amis/{friendship}/accepter', [FriendController::class, 'accept'])->name('friends.accept');
    Route::post('/amis/{friendship}/refuser', [FriendController::class, 'decline'])->name('friends.decline');
    Route::delete('/amis/{friend}', [FriendController::class, 'destroy'])->name('friends.destroy');
    Route::post('/amis/{friend}/defier', [FriendController::class, 'challenge'])->name('friends.challenge');
    Route::post('/amis/defis/{challenge}/accepter', [FriendController::class, 'acceptChallenge'])->name('friends.challenges.accept');
    Route::post('/amis/defis/{challenge}/refuser', [FriendController::class, 'declineChallenge'])->name('friends.challenges.decline');

    // Legacy & Direct fighter actions
    Route::get('/fighters', [FighterController::class, 'index'])->name('fighters.index');
    Route::get('/fighters/create', [FighterController::class, 'create'])->name('fighters.create');
    Route::get('/fighters/roll', [FighterController::class, 'roll'])->name('fighters.roll');
    Route::post('/fighters', [FighterController::class, 'store'])->name('fighters.store');
    Route::get('/fighters/{fighter}', [FighterController::class, 'show'])->name('fighters.show');
    Route::post('/fighters/{fighter}/shop', [FighterController::class, 'shopSpend'])->name('fighters.shop');
    Route::post('/fighters/{fighter}/allocate-stat', [FighterController::class, 'allocateStat'])->name('fighters.allocate-stat');
    Route::post('/fighters/{fighter}/regenerate-portrait', [FighterController::class, 'regeneratePortrait'])->name('fighters.regenerate-portrait');

    // Tournaments (Player views & registration)
    Route::get('/hall-of-fame', [TournamentController::class, 'hallOfFame'])->name('tournaments.hall-of-fame');
    Route::get('/tournaments', [TournamentController::class, 'index'])->name('tournaments.index');
    Route::get('/tournaments/hall-of-fame', [TournamentController::class, 'hallOfFame']);

    // Private Tournament Rooms (Salons privés entre amis avec protection brute-force)
    Route::get('/salons-prives', [PrivateTournamentController::class, 'index'])->name('tournaments.private.index');
    Route::post('/salons-prives', [PrivateTournamentController::class, 'store'])->middleware('throttle:10,1')->name('tournaments.private.store');
    Route::match(['GET', 'POST'], '/salons-prives/rejoindre', [PrivateTournamentController::class, 'join'])->middleware('throttle:15,1')->name('tournaments.private.join');
    Route::get('/salons-prives/{tournament}', [PrivateTournamentController::class, 'lobby'])->name('tournaments.private.lobby');
    Route::post('/salons-prives/{tournament}/lancer', [PrivateTournamentController::class, 'start'])->middleware('throttle:10,1')->name('tournaments.private.start');

    Route::get('/tournaments/{tournament}', [TournamentController::class, 'show'])->name('tournaments.show');
    Route::post('/tournaments/{tournament}/register', [TournamentController::class, 'register'])->name('tournaments.register');
    Route::post('/tournaments/{tournament}/unregister', [TournamentController::class, 'unregister'])->name('tournaments.unregister');

    // Micro-Transaction & Pass Illimité (700 FCFA via Chariow / Mobile Money)
    Route::get('/checkout/pass', [PaymentController::class, 'checkout'])->name('payment.checkout');
    Route::post('/checkout/process', [PaymentController::class, 'process'])->name('payment.process');
    Route::post('/checkout/upload-photo', [PaymentController::class, 'uploadPhoto'])->name('payment.upload-photo');
    Route::post('/checkout/notify-ack/{passRequest}', [PaymentController::class, 'acknowledgeNotification'])->name('payment.ack-notification');
    Route::get('/checkout/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::post('/checkout/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

    // Battles (Playback & Training Duel)
    Route::get('/battles/{battle}', [BattleController::class, 'show'])->name('battles.show');
    Route::post('/battles/training-duel', [BattleController::class, 'trainingDuel'])->name('battles.training');

    // Game Master Admin Cockpit (/admin/...)
    Route::prefix('admin')->name('admin.')->middleware('game_master')->group(function () {
        // Admin Dashboard: stats, active tournaments, real participants
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Tournaments Management (/admin/tournois)
        Route::get('/tournois', [TournamentAdminController::class, 'index'])->name('tournaments.index');
        Route::get('/tournaments', [TournamentAdminController::class, 'index']);
        Route::get('/tournaments/create', [TournamentAdminController::class, 'create'])->name('tournaments.create');
        Route::post('/tournaments', [TournamentAdminController::class, 'store'])->name('tournaments.store');
        Route::get('/tournaments/{tournament}/edit', [TournamentAdminController::class, 'edit'])->name('tournaments.edit');
        Route::put('/tournaments/{tournament}', [TournamentAdminController::class, 'update'])->name('tournaments.update');
        Route::delete('/tournaments/{tournament}', [TournamentAdminController::class, 'destroy'])->name('tournaments.destroy');
        Route::post('/tournaments/{tournament}/end', [TournamentAdminController::class, 'endTournament'])->name('tournaments.end');
        Route::post('/tournaments/{tournament}/fill-bots', [TournamentAdminController::class, 'fillBots'])->name('tournaments.fill-bots');
        Route::post('/tournaments/{tournament}/bracket', [TournamentAdminController::class, 'generateBracket'])->name('tournaments.bracket');
        Route::post('/tournaments/{tournament}/resolve-all', [TournamentAdminController::class, 'resolveAll'])->name('tournaments.resolve-all');

        // User and Character Management (/admin/user)
        Route::get('/user', [UserAdminController::class, 'index'])->name('users.index');
        Route::post('/user/{user}/toggle-suspension', [UserAdminController::class, 'toggleSuspension'])->name('users.toggle-suspension');
        Route::delete('/user/{user}', [UserAdminController::class, 'destroy'])->name('users.destroy');
        Route::delete('/user/{user}/fighter', [UserAdminController::class, 'deleteFighter'])->name('users.delete-fighter');

        // Wizard of Oz Portrait Management (/admin/portraits)
        Route::get('/portraits', [PortraitAdminController::class, 'index'])->name('portraits.index');
        Route::get('/portraits/{fighter}/download-source', [PortraitAdminController::class, 'downloadSourcePhoto'])->name('portraits.download-source');
        Route::post('/portraits/{fighter}/upload', [PortraitAdminController::class, 'upload'])->name('portraits.upload');

        // Pass Verification Requests (/admin/pass-requests)
        Route::get('/pass-requests', [PassRequestAdminController::class, 'index'])->name('pass-requests.index');
        Route::post('/pass-requests/{passRequest}/approve', [PassRequestAdminController::class, 'approve'])->name('pass-requests.approve');
        Route::post('/pass-requests/{passRequest}/reject', [PassRequestAdminController::class, 'reject'])->name('pass-requests.reject');
        Route::delete('/pass-requests/{passRequest}', [PassRequestAdminController::class, 'destroy'])->name('pass-requests.destroy');

        // Admin Profile (/admin/profile)
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    });
});
