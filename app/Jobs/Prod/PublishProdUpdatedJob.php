<?php
namespace App\Jobs\Prod;

use App\Models\Prod;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use RdKafka\Conf;
use RdKafka\Producer;

class PublishProdUpdatedJob implements ShouldQueue
{
    use Queueable;

    public Prod $prod;

    /**
     * Create a new job instance.
     */
    public function __construct(Prod $prod)
    {
        $this->prod = $prod;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {



        $payload = [
            'id'         => $this->prod->id,
            'catid'      => $this->prod->catid,
            'name'       => $this->prod->name,
            'filename'   => $this->prod->filename,
            'file'       => $this->prod->filer,
            'file_url'   => env('APP_URL_COMPLETE') . $this->prod->filer,
            'mime'       => $this->prod->mime,
            'sizer'      => $this->prod->sizer,
            'extension'  => $this->prod->extension,
            'des'        => $this->prod->des,
            'dess'       => $this->prod->dess,
            'img'        => env('APP_URL_COMPLETE') . $this->prod->img,
            'img2'       => env('APP_URL_COMPLETE') . $this->prod->img2,
            'updated_at' => now(),
        ];

        // SQLite payload (relative paths, mirrors Postgres id)
        $payloadsqllite = [
            'id'         => $this->prod->id,
            'catid'      => $this->prod->catid,
            'name'       => $this->prod->name,
            'filer'      => $this->prod->filer,
            'filename'   => $this->prod->filename,
            'des'        => $this->prod->des,
            'dess'       => $this->prod->dess,
            'mime'       => $this->prod->mime,
            'sizer'      => $this->prod->sizer,
            'img'        => $this->prod->img,
            'img2'       => $this->prod->img2,
            'extension'  => $this->prod->extension,
            'updated_at' => now(),
        ];

        $payloadmysql = $payloadsqllite; 
        unset($payloadmysql['id']);

        // ✅ SQLite backup
        try {
            DB::connection('sqlite_backupdb')->table('prods')->updateOrInsert(
                ['id' => $this->prod->id],
                $payload
            );
            Log::info("✅ SQLite backup updated for prod {$this->prod->id}");
        } catch (\Exception $e) {
            Log::error("❌ SQLite backup update failed: " . $e->getMessage(), [
                'prod_id' => $this->prod->id,
                'payload' => $payload,
            ]);
        }


        // ✅ MySQL backup
        try {
            DB::connection('mysql')->table('prods')->updateOrInsert(
                ['originid' => $this->prod->id],
                $payloadmysql
            );
            Log::info("✅ MySQL backup updated for prod {$this->prod->id}");
        } catch (\Exception $e) {
            Log::error("❌ MySQL backup update failed: " . $e->getMessage(), [
                'prod_id' => $this->prod->id,
                'payload' => $payload,
            ]);
        }

        // ✅ Redis cache
        try {
            $redisjson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

            if ($redisjson === false) {
                Log::error('Redis JSON encode failed', [
                    'error'   => json_last_error_msg(),
                    'payload' => $payload,
                ]);
            } else {
                Redis::set("prod:{$this->prod->id}", $redisjson);
                Log::info("✅ Redis updated for prod {$this->prod->id}");
            }
        } catch (\Exception $e) {
            Log::error("❌ Redis update failed: " . $e->getMessage(), [
                'prod_id' => $this->prod->id,
            ]);
        }

        // ✅ Kafka publish
        try {
            $conf = new Conf();
            $conf->set('bootstrap.servers', 'localhost:9092');

            $conf->setDrMsgCb(function ($kafka, $message) {
                if ($message->err) {
                    Log::error("❌ Kafka delivery failed: " . $message->errstr());
                } else {
                    Log::info("✅ Kafka delivered to partition {$message->partition} at offset {$message->offset}");
                }
            });

            $producer = new Producer($conf);
            $topic    = $producer->newTopic('prods_queue');

            $message = json_encode(['event' => 'prod.updated'] + $payload);

            Log::info("📤 Producing update message to Kafka");

            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);

            for ($i = 0; $i < 10; $i++) {
                $producer->poll(100);
            }

            $remaining = $producer->flush(10000);

            if ($remaining === 0) {
                Log::info("✅ Kafka update message sent successfully");
            } else {
                Log::error("❌ {$remaining} update messages failed to send");
            }
        } catch (\Exception $e) {
            Log::error("❌ Kafka exception: " . $e->getMessage());
        }

        Log::info("📦 Prod updated + backups attempted (SQLite/MySQL/Redis/Kafka)", [
            'prod_id' => $this->prod->id,
        ]);
    }
}
