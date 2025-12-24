<?php
namespace App\Jobs\Prod;

use App\Models\Prod;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use RdKafka\Conf;
use RdKafka\Producer;

class PublishProdAddedJob implements ShouldQueue
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

    public function handle(): void
    {
        $payload = [
            'id'         => $this->prod->id,
            'catid'         => $this->prod->catid,
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
            'created_at' => $this->prod->created_at,
            'updated_at' => now(),
        ];

        $payloadsqllite = [
            'id'         => $this->prod->id,
            'catid'         => $this->prod->catid,
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
            'created_at' => $this->prod->created_at,
            'updated_at' => now(),
        ];

        $payloadmysql = [
            'originid'   => $this->prod->id,
            'catid'         => $this->prod->catid,
            'name'       => $this->prod->name,
            'filer'      => $this->prod->filer,
            'filename'   => $this->prod->filename,
            'des'        => $this->prod->des,
            'dess'       => $this->prod->dess,
            'mime'       => $this->prod->mime,
            'sizer'      => $this->prod->sizer,
            'extension'  => $this->prod->extension,
            'img'        => $this->prod->img,
            'img2'       => $this->prod->img2,
            'created_at' => $this->prod->created_at,
            'updated_at' => now(),
        ];

        // ✅ SQLite insert
        try {
            DB::connection('sqlite_backupdb')->table('prods')->insert($payloadsqllite);
            \Log::info("✅ SQLite backup insert succeeded", ['prod_id' => $this->prod->id]);
        } catch (\Exception $e) {
            \Log::error("❌ SQLite backup insert failed: " . $e->getMessage(), [
                'prod_id' => $this->prod->id,
                'payload' => $payloadsqllite,
            ]);
        }

        // ✅ MySQL insert
        try {
            DB::connection('mysql')->table('prods')->insert($payloadmysql);
            \Log::info("✅ MySQL backup insert succeeded", ['prod_id' => $this->prod->id]);
        } catch (\Exception $e) {
            \Log::error("❌ MySQL backup insert failed: " . $e->getMessage(), [
                'prod_id' => $this->prod->id,
                'payload' => $payloadmysql,
            ]);
        }

        // ✅ Redis block
        try {
            $payloadRedis = [
                'originid'  => $this->prod->id,
                'name'      => $this->prod->name,
                'filer'     => $this->prod->filer,
                'filename'  => $this->prod->filename,
                'img'       => $this->prod->img,
                'img2'      => $this->prod->img2,
                'des'       => $this->prod->des,
                'mime'      => $this->prod->mime,
                'sizer'     => $this->prod->sizer,
                'extension' => $this->prod->extension,
            ];

            $redisjson = json_encode($payloadRedis, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

            if ($redisjson === false) {
                \Log::error('Redis JSON encode failed', [
                    'error'   => json_last_error_msg(),
                    'payload' => $payloadRedis,
                ]);
            } else {
                Redis::set("prod:{$this->prod->id}", $redisjson);
                \Log::info('Redis JSON stored successfully', [
                    'key'   => "prod:{$this->prod->id}",
                    'value' => $redisjson,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error("❌ Redis operation failed: " . $e->getMessage(), [
                'prod_id' => $this->prod->id,
            ]);
        }

        // ✅ Kafka block
        try {
            $conf = new Conf();
            $conf->set('bootstrap.servers', 'localhost:9092');

            $conf->setDrMsgCb(function ($kafka, $message) {
                if ($message->err) {
                    \Log::error("❌ Kafka delivery failed: " . $message->errstr());
                } else {
                    \Log::info("✅ Kafka delivered to partition {$message->partition} at offset {$message->offset}");
                }
            });

            $producer = new Producer($conf);
            $topic    = $producer->newTopic('prods_queue');

            $message = json_encode(['event' => 'prod.added'] + $payload);

            \Log::info("📤 Producing message to Kafka");

            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);

            \Log::info("🔄 Polling for delivery...");
            for ($i = 0; $i < 10; $i++) {
                $producer->poll(100);
            }

            \Log::info("🚿 Flushing...");
            $remaining = $producer->flush(10000);

            if ($remaining === 0) {
                \Log::info("✅ Kafka message sent successfully");
            } else {
                \Log::error("❌ {$remaining} messages failed to send");
            }
        } catch (\Exception $e) {
            \Log::error("❌ Kafka exception: " . $e->getMessage());
        }

        \Log::info("📦 Published Prod + backups attempted (SQLite/MySQL/Redis/Kafka)", [
            'prod_id' => $this->prod->id,
        ]);
    }

}
 
