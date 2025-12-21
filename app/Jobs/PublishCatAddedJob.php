<?php

namespace App\Jobs;

use App\Models\Cat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
//use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use RdKafka\Conf;
use RdKafka\Producer;
use Illuminate\Support\Facades\Redis; 

class PublishCatAddedJob implements ShouldQueue
{
    use Queueable;

    public Cat $cat;

    /**
     * Create a new job instance.
     */
    public function __construct(Cat $cat)
    {
        $this->cat = $cat;
    }

    public function handle(): void
    {
        $payload = [
            'id'         => $this->cat->id,
            'name'       => $this->cat->name,
            'filename'   => $this->cat->filename,
            'file'       => $this->cat->filer,
            'file_url'   => env('APP_URL_COMPLETE') . $this->cat->filer,
            'mime'       => $this->cat->mime,
            'sizer'      => $this->cat->sizer,
            'extension'  => $this->cat->extension,
            'des'        => $this->cat->des,
            'dess'       => $this->cat->dess,
            'created_at' => $this->cat->created_at,
            'updated_at' => now(),
        ];

        $payload2 = [
            'id'         => $this->cat->id,
            'name'       => $this->cat->name,
            'filer'       => $this->cat->filer,
            'filename'   => $this->cat->filename,
            'des'        => $this->cat->des,
            'dess'       => $this->cat->dess,
            'mime'       => $this->cat->mime,
            'sizer'      => $this->cat->sizer,
            'extension'  => $this->cat->extension,
            'created_at' => $this->cat->created_at,
            'updated_at' => now(),
        ];

        // Insert into backup SQLite DB
        DB::connection('sqlite_backupdb')->table('cats_backup')->insert($payload2);


        $payload3 = [
            'originid'    => $this->cat->id,      
            'name'        => $this->cat->name,
            'filer'       => $this->cat->filer,
            'filename'    => $this->cat->filename,
            'des'         => $this->cat->des,
            'dess'        => $this->cat->dess,
            'mime'        => $this->cat->mime,
            'sizer'       => $this->cat->sizer,
            'extension'   => $this->cat->extension,
            'created_at'  => $this->cat->created_at,
            'updated_at'  => now(),
        ];

        // Insert into MySQL backup DB
        DB::connection('mysql')->table('cats')->insert($payload3);

        $payloadRedis = 
        [
            'originid'    => $this->cat->id,      
            'name'        => $this->cat->name,
            'filer'       => $this->cat->filer,
            'filename'    => $this->cat->filename,
            'des'         => $this->cat->des,
            'mime'        => $this->cat->mime,
            'sizer'       => $this->cat->sizer,
            'extension'   => $this->cat->extension,
        ]; 

        $redisjson = json_encode($payloadRedis, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        if ($redisjson === false) {
            logger()->error('Redis JSON encode failed', [
                'error'   => json_last_error_msg(),
                'payload' => $payloadRedis,
            ]);
        } else {
            Redis::set("cat:{$this->cat->id}", $redisjson);

            // ✅ log success
            logger()->info('Redis JSON stored successfully', [
                'key'   => "cat:{$this->cat->id}",
                'value' => $redisjson,
            ]);
        }




        // Publish to Redis channel
        /*
        Redis::publish('cats', json_encode(['event' => 'cat.added'] + $payload));
        */
        try 
        {
            $conf = new \RdKafka\Conf();
            $conf->set('bootstrap.servers', 'localhost:9092');
            
            // CRITICAL: Add delivery report callback
            $conf->setDrMsgCb(function ($kafka, $message) {
                if ($message->err) {
                    \Log::error("❌ Kafka delivery failed: " . $message->errstr());
                } else {
                    \Log::info("✅ Kafka delivered to partition {$message->partition} at offset {$message->offset}");
                }
            });

            $producer = new \RdKafka\Producer($conf);
            $topic = $producer->newTopic('cats_queue');

            $message = json_encode(['event' => 'cat.added'] + $payload);
            
            \Log::info("📤 Producing message to Kafka");
            
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
            
            // CRITICAL: Must call poll() to actually send messages
            \Log::info("🔄 Polling for delivery...");
            for ($i = 0; $i < 10; $i++) {
                $producer->poll(100);
            }
            
            // Flush returns number of messages still in queue (0 = success)
            \Log::info("🚿 Flushing...");
            $remaining = $producer->flush(10000);
            
            if ($remaining === 0) {
                \Log::info("✅ Kafka message sent successfully");
            } else {
                \Log::error("❌ {$remaining} messages failed to send");
            }
            
        } 
        catch (\Exception $e) 
        {
            \Log::error("❌ Kafka exception: " . $e->getMessage());
        }

        \Log::info("🐱 Published By rabitMQ" . json_encode($payload) . " + backed up to SQLite: " . json_encode($payload2));
    }

}
