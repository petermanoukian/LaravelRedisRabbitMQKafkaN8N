<?php

namespace App\Listeners;

use App\Events\CatUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Jobs\PublishCatUpdatedJob;

class HandleCatUpdated
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
    public function handle(CatUpdated $event): void
    {
        Log::info('🐱 Cat updated Line 26: ' . $event->cat->id);

        // Laravel internal job queue
        PublishCatUpdatedJob::dispatch($event->cat)->onQueue('cats_queue');

    }
}
