<?php

namespace App\Providers;

use App\Models\Domain;
use App\Models\Subdomain;
use App\Observers\DomainObserver;
use App\Observers\SubdomainObserver;
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
        Domain::observe(DomainObserver::class);
        Subdomain::observe(SubdomainObserver::class);
    }
}
