<?php
namespace App\Jobs\Prod;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use RdKafka\Conf;
use RdKafka\Producer;

class PublishProdDeletedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $id;
    public string $name;
    public string $des;

    /**
     * Create a new job instance.
     */
    public function __construct(int $id, string $name, string $des)
    {
        $this->id   = $id;
        $this->name = $name;
        $this->des  = $des;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $payload = [
            'event' => 'prod.deleted',
            'id'    => $this->id,
            'name'  => $this->name,
            'des'   => $this->des,
        ];

        // ✅ SQLite backup: DELETE the row completely
        DB::connection('sqlite_backupdb')
            ->table('prods')
            ->where('id', $this->id)
            ->delete();

        // ✅ MySQL backup: DELETE the row completely
        DB::connection('mysql')
            ->table('prods')
            ->where('originid', $this->id)
            ->delete();

        // ✅ Kafka publish
        try {
            $conf = new Conf();
            $conf->set('bootstrap.servers', 'localhost:9092');

            $producer = new Producer($conf);
            $topic    = $producer->newTopic('prods_queue');

            $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($payload));

            for ($i = 0; $i < 10; $i++) {
                $producer->poll(100);
            }
            $producer->flush(10000);

        } catch (\Exception $e) {
            \Log::error("❌ Kafka exception on prod delete: " . $e->getMessage());
        }

        \Log::info("📦 Prod delete published + removed from SQLite/MySQL: " . json_encode($payload));
    }
}
