<?php

namespace App\Providers;

use App\Models\IpTournamentQuota;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        if ($this->app->environment('production') || env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        // Custom Login Rate Limiter: exempt whitelisted developer IP and relax limits
        RateLimiter::for('login', function (Request $request) {
            $ip = $request->ip();

            if (IpTournamentQuota::isWhitelisted($ip)) {
                return Limit::none();
            }

            return Limit::perMinute(30)->by($request->input('login').$ip);
        });
    }
}
