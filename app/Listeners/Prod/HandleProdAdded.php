<?php
namespace App\Listeners\Prod;

use App\Events\Prod\ProdAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Jobs\Prod\PublishProdAddedJob;

class HandleProdAdded
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
    public function handle(ProdAdded $event): void
    {
        Log::info('📦 New Prod added Line 26: ' . $event->prod->id);
        // Laravel internal job queue
        //PublishProdAddedJob::dispatch($event->prod)->onQueue('prods_queue');
        PublishProdAddedJob::dispatch($event->prod) ->onConnection('redis') ->onQueue('redis_broadcast');
    }
}
