<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use App\Models\UserModel;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class MongoDBSanctumServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Use our custom PersonalAccessToken model
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        
        // Configure auth to use our UserModel
        $this->app['config']->set('auth.providers.users.model', UserModel::class);
    }
}