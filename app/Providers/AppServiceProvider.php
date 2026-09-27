<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
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
        Blade::directive('money', fn (string $expression) => "<?php echo money($expression); ?>");

        RateLimiter::for('device-me', fn () => Limit::perMinute(30)->by(request()->ip() ?? 'unknown'));

        if (class_exists(\App\Models\Transaction::class) && class_exists(\App\Observers\TransactionObserver::class)) {
            \App\Models\Transaction::observe(\App\Observers\TransactionObserver::class);
        }
    }
}
