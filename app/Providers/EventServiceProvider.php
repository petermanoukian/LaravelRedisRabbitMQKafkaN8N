<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use App\Events\CatAdded;
use App\Listeners\HandleCatAdded;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */

    /*
    protected $listen = [
        \App\Events\CatAdded::class => [
            \App\Listeners\HandleCatAdded::class,
        ],
    ];
*/

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

