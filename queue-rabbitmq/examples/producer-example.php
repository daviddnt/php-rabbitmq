<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Queue\Producer;

// Create producer
$producer = new Producer('logs');

// Example 1: Publish single message
echo "=== Example 1: Single Message ===\n";
$producer->publish([
    'type' => 'user_login',
    'user_id' => 123,
    'timestamp' => time()
]);

// Example 2: Publish batch of messages
echo "\n=== Example 2: Batch Messages ===\n";
$messages = [];
for ($i = 1; $i <= 100; $i++) {
    $messages[] = [
        'type' => 'log_entry',
        'message' => "Log entry #{$i}",
        'level' => ['info', 'warning', 'error'][rand(0, 2)],
        'timestamp' => time()
    ];
}

// Publish 100 messages in batches of 30, with 2 seconds interval
$producer->publishBatch($messages, 30, 2);

$producer->close();
echo "\nDone!\n";
