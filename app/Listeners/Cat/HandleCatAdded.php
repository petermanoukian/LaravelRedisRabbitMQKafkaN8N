<?php
namespace App\Listeners\Cat;

use App\Events\Cat\CatAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Jobs\Cat\PublishCatAddedJob;


class HandleCatAdded
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }


    public function handle(CatAdded $event): void
    { 
        Log::info('🐱 New Cat added Line 26: ' . $event->cat->id);
        // Laravel internal job queue
        PublishCatAddedJob::dispatch($event->cat)->onQueue('cats_queue');

    }

}
