<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

// Contracts
use App\Repositories\Contracts\CatRepositoryInterface;
use App\Services\Interface\CatServiceInterface;

// Implementations
use App\Repositories\CatRepository;   // adjust namespace if different
use App\Services\CatService;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind interfaces to implementations
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CatRepositoryInterface::class, CatRepository::class);
        $this->app->bind(CatServiceInterface::class, CatService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
