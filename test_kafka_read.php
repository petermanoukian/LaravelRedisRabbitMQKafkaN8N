<?php
require __DIR__ . '/vendor/autoload.php';

$conf = new \RdKafka\Conf();
$conf->set('group.id', 'debug-test-' . time());
$conf->set('bootstrap.servers', 'localhost:9092');
$conf->set('auto.offset.reset', 'earliest');
$conf->set('debug', 'consumer,cgrp,topic,fetch');

$consumer = new \RdKafka\KafkaConsumer($conf);
$consumer->subscribe(['cats_queue']);

echo "🔍 Attempting to read from cats_queue...\n";
echo "Group ID: debug-test-" . time() . "\n\n";

$found = 0;
for ($i = 0; $i < 50; $i++) {
    echo "Attempt {$i}... ";
    $message = $consumer->consume(3000);
    
    if ($message->err === RD_KAFKA_RESP_ERR_NO_ERROR) {
        $found++;
        echo "✅ GOT MESSAGE #{$found}\n";
        echo "   Offset: {$message->offset}\n";
        echo "   Payload: " . substr($message->payload, 0, 100) . "...\n\n";
    } elseif ($message->err === RD_KAFKA_RESP_ERR__PARTITION_EOF) {
        echo "📭 End of partition\n";
        break;
    } elseif ($message->err === RD_KAFKA_RESP_ERR__TIMED_OUT) {
        echo "⏳ Timeout\n";
    } else {
        echo "❌ Error: " . $message->errstr() . "\n";
    }
}

echo "\n📊 Total messages found: {$found}\n";