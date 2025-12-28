<?php
namespace App\Jobs\Cat;

use App\Models\Cat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use RdKafka\Conf;
use RdKafka\Producer;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class PublishCatUpdatedJob implements ShouldQueue
{
    use Queueable;

    public Cat $cat;

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

        // SQLite payload
        $payloadsqlite = [
            'id'         => $this->cat->id,
            'name'       => $this->cat->name,
            'filer'      => $this->cat->filer,
            'filename'   => $this->cat->filename,
            'des'        => $this->cat->des,
            'dess'       => $this->cat->dess,
            'mime'       => $this->cat->mime,
            'sizer'      => $this->cat->sizer,
            'extension'  => $this->cat->extension,
            'created_at' => $this->cat->created_at,
            'updated_at' => now(),
        ];

        // MySQL payload = SQLite payload minus id, plus originid
        $payloadmysql = $payloadsqlite;
        unset($payloadmysql['id']);
        $payloadmysql['originid'] = $this->cat->id;

        // ✅ SQLite update
        try {
            DB::connection('sqlite_backupdb')
                ->table('cats')
                ->where('id', $this->cat->id)
                ->update($payloadsqlite);
            \Log::info("✅ SQLite update succeeded for cat {$this->cat->id}");
        } catch (\Exception $e) {
            \Log::error("❌ SQLite update failed: " . $e->getMessage(), [
                'cat_id' => $this->cat->id,
                'payload' => $payloadsqlite,
            ]);
        }

        // ✅ MySQL update
        try {
            DB::connection('mysql')
                ->table('cats')
                ->where('originid', $this->cat->id)
                ->update($payloadmysql);
            \Log::info("✅ MySQL update succeeded for cat {$this->cat->id}");
        } catch (\Exception $e) {
            \Log::error("❌ MySQL update failed: " . $e->getMessage(), [
                'cat_id' => $this->cat->id,
                'payload' => $payloadmysql,
            ]);
        }


        // ✅ RabbitMQ publish for N8N
        $rabbitPayloadN8N = [
            'event' => 'cat.updated',
            'id'    => $this->cat->id,
            'name'  => $this->cat->name,
            'filename' => $this->cat->filename,
            'file'     => $this->cat->filer,
            'file_url' => env('APP_URL_COMPLETE') . $this->cat->filer,
            'mime'     => $this->cat->mime,
            'sizer'    => $this->cat->sizer,
            'extension'=> $this->cat->extension,
            'des'      => $this->cat->des,
            'dess'     => $this->cat->dess,
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
            $channel->queue_declare('cats_queue_n8n', false, true, false, false);

            $msg = new AMQPMessage(
                json_encode($rabbitPayloadN8N),
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                ]
            );

            $channel->basic_publish($msg, '', 'cats_queue_n8n');
            $channel->close();
            $connection->close();
            \Log::info("✅ RabbitMQ update published for cat {$this->cat->id}");
        } catch (\Throwable $e) {
            \Log::error("❌ RabbitMQ update failed: " . $e->getMessage());
        }




        // ✅ Kafka publish
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
            $topic = $producer->newTopic('cats_queue');

            $message = json_encode(['event' => 'cat.updated'] + $payload);

            \Log::info("📤 Producing update message to Kafka");

            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);

            \Log::info("🔄 Polling for delivery...");
            for ($i = 0; $i < 10; $i++) {
                $producer->poll(100);
            }

            \Log::info("🚿 Flushing...");
            $remaining = $producer->flush(10000);

            if ($remaining === 0) {
                \Log::info("✅ Kafka update message sent successfully");
            } else {
                \Log::error("❌ {$remaining} update messages failed to send");
            }
        } catch (\Exception $e) {
            \Log::error("❌ Kafka exception on update: " . $e->getMessage());
        }

        \Log::info("🐱 Cat update published + backed up to SQLite: " . json_encode($payloadsqlite));
    }
}
