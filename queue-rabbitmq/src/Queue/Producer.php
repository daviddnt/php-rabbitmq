<?php

declare(strict_types=1);

namespace App\Queue;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Exception;

class Producer
{
    private $channel;
    private $connection;
    private string $queueName;

    public function __construct(string $queueName = 'logs')
    {
        $config = RabbitMQConfig::getInstance();

        $this->connection = new AMQPStreamConnection(
            $config->getHost(),
            $config->getPort(),
            $config->getUser(),
            $config->getPassword()
        );

        $this->channel = $this->connection->channel();
        $this->queueName = $queueName;
        $this->channel->queue_declare($queueName, false, true, false, false);

        $this->log("Producer initialized for queue: {$queueName}");
    }

    /**
     * Publish a single message
     *
     * @param array $data Message data
     * @return bool Success status
     */
    public function publish(array $data): bool
    {
        try {
            $message = new AMQPMessage(
                json_encode($data),
                ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
            );

            $this->channel->basic_publish($message, '', $this->queueName);
            return true;
        } catch (Exception $e) {
            $this->log("Error publishing message: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Publish multiple messages in batch
     *
     * @param array $messages Array of message data
     * @param int $batchSize Number of messages per batch
     * @param int $intervalSeconds Interval between batches
     * @return int Number of successfully published messages
     */
    public function publishBatch(
        array $messages,
        int $batchSize = 100,
        int $intervalSeconds = 0
    ): int {
        $total = count($messages);
        $published = 0;
        $batches = array_chunk($messages, $batchSize);

        $this->log("Publishing {$total} messages in " . count($batches) . " batches");

        foreach ($batches as $index => $batch) {
            $batchNumber = $index + 1;
            $this->log("Publishing batch #{$batchNumber} with " . count($batch) . " messages");

            foreach ($batch as $data) {
                if ($this->publish($data)) {
                    $published++;
                }
            }

            if ($intervalSeconds > 0 && $batchNumber < count($batches)) {
                $this->log("Waiting {$intervalSeconds} seconds before next batch...");
                sleep($intervalSeconds);
            }
        }

        $this->log("Published {$published}/{$total} messages successfully");
        return $published;
    }

    public function close(): void
    {
        try {
            if ($this->channel) {
                $this->channel->close();
            }
            if ($this->connection) {
                $this->connection->close();
            }
            $this->log("Producer closed successfully");
        } catch (Exception $e) {
            $this->log("Error closing producer: " . $e->getMessage(), 'ERROR');
        }
    }

    private function log(string $message, string $level = 'INFO'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        echo "[{$timestamp}] [{$level}] Producer: {$message}\n";
    }

    public function __destruct()
    {
        $this->close();
    }
}
