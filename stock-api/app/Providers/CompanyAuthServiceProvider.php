<?php

namespace App\Providers;

use App\Guards\CompanyGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class CompanyAuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Register custom guard
        Auth::extend('company', function ($app, $name, array $config) {
            return new CompanyGuard(
                Auth::createUserProvider($config['provider']),
                $app->make('request')
            );
        });
    }
}
