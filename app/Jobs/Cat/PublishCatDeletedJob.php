<?php
namespace App\Jobs\Cat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use RdKafka\Conf;
use RdKafka\Producer;

class PublishCatDeletedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $id;
    public string $name;
    public string $des;

    public function __construct(int $id, string $name, string $des)
    {
        $this->id   = $id;
        $this->name = $name;
        $this->des  = $des;
    }

    public function handle(): void
    {
        $payload = [
            'event' => 'cat.deleted',
            'id'    => $this->id,
            'name'  => $this->name,
            'des'   => $this->des,
        ];

        // ✅ SQLite backup: DELETE the row completely
        DB::connection('sqlite_backupdb')
            ->table('cats')
            ->where('id', $this->id)
            ->delete();
        //DB::connection('sqlite_backupdb') ->table('prods') ->where('catid', $this->id) ->delete();

        DB::connection('mysql')
            ->table('cats')
            ->where('originid', $this->id)
            ->delete();
        /*
        DB::connection('mysql') ->table('prods') 
        ->where('catid', $this->id) ->delete();
        */

        try {
            $conf = new Conf();
            $conf->set('bootstrap.servers', 'localhost:9092');

            $producer = new Producer($conf);
            $topic    = $producer->newTopic('cats_queue');

            $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($payload));

            for ($i = 0; $i < 10; $i++) {
                $producer->poll(100);
            }
            $producer->flush(10000);

        } catch (\Exception $e) {
            \Log::error("❌ Kafka exception on delete: " . $e->getMessage());
        }

        \Log::info("🐱 Cat delete published + removed from SQLite: " . json_encode($payload));
    }
}
