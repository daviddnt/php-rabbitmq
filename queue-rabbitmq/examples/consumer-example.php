<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Queue\Consumer;

// Create consumer
$consumer = new Consumer('logs');

// Callback function to process batches
$callback = function (array $batch): void {
    echo "Processing batch with " . count($batch) . " messages:\n";

    foreach ($batch as $index => $message) {
        echo "  [{$index}] Type: {$message['type']}, Message: {$message['message']}\n";
    }

    echo "Batch processed successfully!\n\n";
};

// Consume 100 messages total, in batches of 30, with 10 seconds interval
echo "Starting consumer...\n";
$consumer->consume(
    callback: $callback,
    totalMessages: 100,
    intervalSeconds: 10,
    batchSize: 30
);

$consumer->close();
echo "Consumer finished!\n";
