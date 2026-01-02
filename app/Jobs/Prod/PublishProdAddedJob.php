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

        $payloadmysql = $payloadsqllite; 
        unset($payloadmysql['id']); 
        $payloadmysql['originid'] = $this->prod->id;

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

// ✅ N8N Webhook notification
        try {

            \Log::info('🔎 N8N_DOMAIN value from env()', [
                'N8N_DOMAIN' => env('N8N_DOMAIN')
            ]);

            $webhookUrl = env('N8N_DOMAIN') . '/webhook/kafkaprod';

            
            $webhookPayload = [
                'event' => 'prod.added',
                'data' => $payload
            ];
            
            $ch = curl_init($webhookUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookPayload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                \Log::info("✅ N8N webhook called successfully");
            } else {
                \Log::error("❌ N8N webhook failed", ['http_code' => $httpCode, 'response' => $response]);
            }
        } catch (\Exception $e) {
            \Log::error("❌ N8N webhook exception: " . $e->getMessage());
        }




        /*
        try {
            $conf = new Conf();
            //$conf->set('bootstrap.servers', 'localhost:9092');
            $conf->set('bootstrap.servers', '127.0.0.1:9092');

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

        */

        \Log::info("📦 Published Prod + backups attempted (SQLite/MySQL/Redis/Kafka)", [
            'prod_id' => $this->prod->id,
        ]);
    }

}
 
