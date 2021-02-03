<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        ResetPassword::createUrlUsing(function($user, string $token){
          return env('FRONTEND_URL').env('FRONTEND_RESET_PASSWORD').'/'.$token.'?email='.urlencode(strtolower($user->getEmailForPasswordReset()));
        });

        VerifyEmail::createUrlUsing(function($user){
          return env('FRONTEND_URL').env('FRONTEND_VERIFY_EMAIL').'/'.$user->getKey().'/'.sha1($user->getEmailForPasswordReset());
        });
    }
}
