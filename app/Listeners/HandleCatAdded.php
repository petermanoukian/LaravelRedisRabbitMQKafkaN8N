<?php

namespace App\Listeners;

use App\Events\CatAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Jobs\PublishCatAddedJob;


class HandleCatAdded
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */


    public function handle(CatAdded $event): void
    {
        
        Log::info('🐱 New Cat added Line 26: ' . $event->cat->id);
        // Laravel internal job queue
        PublishCatAddedJob::dispatch($event->cat)->onQueue('cats_queue');

    }



}
