<?php
namespace App\Jobs\Cat;
use App\Models\Cat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
//use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use RdKafka\Conf;
use RdKafka\Producer;
use Illuminate\Support\Facades\Redis; 
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


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

        $payloadsqlite = [
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

        try 
        {
            DB::connection('sqlite_backupdb')->table('cats')->insert($payloadsqlite);
        } 
        catch (\Exception $e) {
            \Log::error("❌ SQLite insert failed: " . $e->getMessage(), [
                'cat_id' => $this->cat->id,
                'payload' => $payloadsqlite,
            ]);
        }

        $payloadmysql = $payloadsqlite;
        unset($payloadmysql['id']);
        $payloadmysql['originid'] = $this->cat->id;

        try {
            DB::connection('mysql')->table('cats')->insert($payloadmysql);
        } catch (\Exception $e) {
            \Log::error("❌ MySQL insert failed: " . $e->getMessage(), [
                'catid' => $this->cat->id,
                'payload' => $payloadmysql,
            ]);
        }

        try {
            $payloadRedis = [
                'originid'  => $this->cat->id,
                'name'      => $this->cat->name,
                'filer'     => $this->cat->filer,
                'filename'  => $this->cat->filename,
                'des'       => $this->cat->des,
                'mime'      => $this->cat->mime,
                'sizer'     => $this->cat->sizer,
                'extension' => $this->cat->extension,
            ];

            $redisjson = json_encode($payloadRedis, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

            if ($redisjson === false) {
                \Log::error('Redis JSON encode failed', [
                    'error'   => json_last_error_msg(),
                    'payload' => $payloadRedis,
                ]);
            } else {
                Redis::set("cat:{$this->cat->id}", $redisjson);
                /*
                \Log::info('Redis JSON stored successfully', [
                    'key'   => "cat:{$this->cat->id}",
                    'value' => $redisjson,
                ]);
                */
            }
        } 
        catch (\Exception $e) {
            // Catch connection or command errors
            \Log::error("❌ Redis operation failed: " . $e->getMessage(), [
                'cat_id' => $this->cat->id,
            ]);
        }


        // Publish to RabbitMQ for N8N (separate queue from Laravel worker)
    
        $rabbitPayloadN8N = [
            'event' => 'cat.added',
            'id' => $this->cat->id,
            'name' => $this->cat->name,
            'filename' => $this->cat->filename,
            'file' => $this->cat->filer,
            'file_url' => env('APP_URL_COMPLETE') . $this->cat->filer,
            'mime' => $this->cat->mime,
            'sizer' => $this->cat->sizer,
            'extension' => $this->cat->extension,
            'des' => $this->cat->des,
            'dess' => $this->cat->dess,
            'created_at' => $this->cat->created_at,
            'updated_at' => now(),
        ];


        try {
            $connection = new AMQPStreamConnection(
                env('RABBITMQ_HOST'),
                env('RABBITMQ_PORT'),
                env('RABBITMQ_USER'),
                env('RABBITMQ_PASSWORD'),
                env('RABBITMQ_VHOST', '/')
            );

            $channel = $connection->channel();

            // IMPORTANT: queue must exist and be durable
            $channel->queue_declare(
                'cats_queue_n8n',
                false, // passive
                true,  // durable
                false, // exclusive
                false  // auto-delete
            );

            $msg = new AMQPMessage(
                json_encode($rabbitPayloadN8N),
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                ]
            );

            $channel->basic_publish($msg, '', 'cats_queue_n8n');
            /*
            \Log::info('✅ Line 170 Plain RabbitMQ message published for n8n', [
                'cat_id' => $this->cat->id,
                'rabbitPayloadN8N' => $rabbitPayloadN8N,
            ]);
            */
            $channel->close();
            $connection->close();

        } 
        catch (\Throwable $e) {
            \Log::error('❌ RabbitMQ publish failed', [
                'error' => $e->getMessage(),
            ]);
        }


        // Publish to Redis channel
        /*
        Redis::publish('cats', json_encode(['event' => 'cat.added'] + $payload));
        */
        try 
        {
            $conf = new \RdKafka\Conf();
            //$conf->set('bootstrap.servers', 'localhost:9092');
            $conf->set('bootstrap.servers', '127.0.0.1:9092');
            
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
            
            // \Log::info("📤 Producing message to Kafka");
            
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
            
            // CRITICAL: Must call poll() to actually send messages
            // \Log::info("🔄 Polling for delivery...");
            for ($i = 0; $i < 10; $i++) {
                $producer->poll(100);
            }
            
            // Flush returns number of messages still in queue (0 = success)
            // \Log::info("🚿 Flushing...");
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

        // \Log::info("🐱 Published By rabitMQ" . json_encode($payload) . " + backed up to SQLite: " . json_encode($payloadsqlite));
    }

}
