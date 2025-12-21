<?php

namespace App\Jobs;

use App\Models\Cat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use RdKafka\Conf;
use RdKafka\Producer;

class PublishCatUpdatedJob implements ShouldQueue
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

    /**
     * Execute the job.
     */
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

        // Update backup SQLite DB
        DB::connection('sqlite_backupdb')
            ->table('cats_backup')
            ->where('id', $this->cat->id)
            ->update($payload2);

        $payload3 = [
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

        // Update backup MySQL DB, matching on originid
        DB::connection('mysql')
            ->table('cats')
            ->where('originid', $this->cat->id)
            ->update($payload3);


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

        \Log::info("🐱 Cat update published + backed up to SQLite: " . json_encode($payload2));
    }
}
