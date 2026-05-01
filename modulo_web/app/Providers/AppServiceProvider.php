<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use App\Models\Vaccine;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

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
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        Paginator::useBootstrap();

        View::composer('layouts.app', function ($view) {
            $alertCount = Cache::remember('alert_badge_count', 300, function () {
                return Vaccine::selectRaw('rfid_tag, vaccine_type_id, MAX(vaccination_date) as last_vax')
                    ->groupBy('rfid_tag', 'vaccine_type_id')
                    ->get()
                    ->filter(fn($r) => Carbon::parse($r->last_vax)->addDays(150)->isPast())
                    ->count();
            });
            $view->with('alertCount', $alertCount);
        });
    }
}
