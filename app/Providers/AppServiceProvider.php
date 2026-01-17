<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

// Contracts
use App\Repositories\Contracts\CatRepositoryInterface;
use App\Services\Interface\CatServiceInterface;
use App\Repositories\CatRepository;   // adjust namespace if different
use App\Services\CatService;
use App\Services\Interface\ProdServiceInterface;
use App\Repositories\Contracts\ProdRepositoryInterface;
use App\Repositories\ProdRepository;
use App\Services\ProdService;
use App\Repositories\Contracts\ProdorderRepositoryInterface; 
use App\Services\Interface\ProdorderServiceInterface; 
// Implementations 
use App\Repositories\ProdorderRepository; 
use App\Services\ProdorderService;

// Implementations              

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
        $this->app->bind(ProdRepositoryInterface::class, ProdRepository::class);
        $this->app->bind(ProdServiceInterface::class, ProdService::class);

        $this->app->bind(ProdorderRepositoryInterface::class, ProdorderRepository::class); 

        $this->app->bind(ProdorderServiceInterface::class, ProdorderService::class);
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
