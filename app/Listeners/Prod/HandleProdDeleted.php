<?php
namespace App\Listeners\Prod;

use App\Events\Prod\ProdDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Jobs\Prod\PublishProdDeletedJob;
use Illuminate\Support\Facades\Log;

class HandleProdDeleted
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
    public function handle(ProdDeleted $event): void
    {
        PublishProdDeletedJob::dispatch($event->id, $event->name, $event->des)
            ->onQueue('prods_queue');
    }
}
