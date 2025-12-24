<?php
namespace App\Listeners\Cat;

use App\Events\Cat\CatDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Jobs\Cat\PublishCatDeletedJob;
use Illuminate\Support\Facades\Log;

class HandleCatDeleted  
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }


    public function handle(CatDeleted $event): void
    {
        PublishCatDeletedJob::dispatch($event->id, $event->name, $event->des)
            ->onQueue('cats_queue');
    }

}
