<?php
namespace App\Listeners\Prod;

use App\Events\Prod\ProdUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Jobs\Prod\PublishProdUpdatedJob;

class HandleProdUpdated
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
    public function handle(ProdUpdated $event): void
    {
        Log::info('📦 Prod updated Line 26: ' . $event->prod->id);

        // Laravel internal job queue
        PublishProdUpdatedJob::dispatch($event->prod)->onQueue('prods_queue');
    }
}
