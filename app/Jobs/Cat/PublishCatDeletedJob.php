<?php
namespace App\Jobs\Cat;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use RdKafka\Conf;
use RdKafka\Producer;
use PhpAmqpLib\Connection\AMQPStreamConnection; 
use PhpAmqpLib\Message\AMQPMessage;

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
        try {
            DB::connection('sqlite_backupdb')
                ->table('cats')
                ->where('id', $this->id)
                ->delete();
            \Log::info("✅ SQLite delete succeeded for cat {$this->id}");
        } catch (\Exception $e) {
            \Log::error("❌ SQLite delete failed: " . $e->getMessage(), [
                'cat_id' => $this->id,
            ]);
        }

        // ✅ MySQL backup: DELETE the row completely
        try {
            DB::connection('mysql')
                ->table('cats')
                ->where('originid', $this->id)
                ->delete();
            \Log::info("✅ MySQL delete succeeded for cat {$this->id}");
        } catch (\Exception $e) {
            \Log::error("❌ MySQL delete failed: " . $e->getMessage(), [
                'cat_id' => $this->id,
            ]);
        }


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
                json_encode($payload),
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                ]
            );

            $channel->basic_publish($msg, '', 'cats_queue_n8n');
            $channel->close();
            $connection->close();
            \Log::info("✅ RabbitMQ delete published for cat {$this->id}");
        } catch (\Throwable $e) {
            \Log::error("❌ RabbitMQ delete failed: " . $e->getMessage());
        }




        // ✅ Kafka publish
        try {
            $conf = new Conf();
            //$conf->set('bootstrap.servers', 'localhost:9092');
            $conf->set('bootstrap.servers', '127.0.0.1:9092');
            $producer = new Producer($conf);
            $topic    = $producer->newTopic('cats_queue');

            $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($payload));

            for ($i = 0; $i < 10; $i++) {
                $producer->poll(100);
            }
            $producer->flush(10000);

            \Log::info("✅ Kafka delete message sent for cat {$this->id}");
        } catch (\Exception $e) {
            \Log::error("❌ Kafka exception on delete: " . $e->getMessage(), [
                'cat_id' => $this->id,
            ]);
        }

        \Log::info("🐱 Cat delete published + attempted (SQLite/MySQL/Kafka)", [
            'cat_id' => $this->id,
            'payload' => $payload,
        ]);
    }
}
