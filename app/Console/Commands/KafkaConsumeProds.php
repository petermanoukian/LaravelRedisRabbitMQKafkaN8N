<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class KafkaConsumeProds extends Command
{
    protected $signature = 'kafka:consume-prods';
    protected $description = 'Consume prods_queue messages via shell';

    public function handle(): int
    {
        $this->info("Starting Kafka consumer for prods_queue...");

        $kafkaPath = env('KAFKA_PATH', 'C:\My-Documents\kafka_2.13-4.1.1');
        $command = "cd {$kafkaPath} && .\\bin\\windows\\kafka-console-consumer.bat --topic prods_queue --bootstrap-server localhost:9092 --from-beginning";

        $process = popen($command, 'r');

        if (!$process) {
            $this->error("Failed to start consumer");
            return Command::FAILURE;
        }

        $messageCount = 0;

        while (!feof($process)) {
            $line = fgets($process);

            if ($line === false) {
                continue;
            }

            $line = trim($line);

            if (empty($line) || strpos($line, 'ERROR') !== false) {
                continue;
            }

            $payload = json_decode($line, true);

            if ($payload !== null) {
                $messageCount++;
                $this->info("--------------------------------------------------");
                $this->info("✅ Message #{$messageCount}");
                $this->info("Event: " . ($payload['event'] ?? 'N/A'));
                $this->line("Prod ID: " . ($payload['id'] ?? 'N/A'));
                $this->line("Prod Name: " . ($payload['name'] ?? 'N/A'));
                $this->line("Details: " . ($payload['des'] ?? 'N/A'));
                $this->line("Created At: " . ($payload['created_at'] ?? 'N/A'));
                $this->info("--------------------------------------------------");
            }
        }

        pclose($process);

        return Command::SUCCESS;
    }
}

