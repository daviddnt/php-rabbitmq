<?php

declare(strict_types=1);

namespace App\Queue;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use Exception;

class Consumer
{
    private const DEFAULT_BATCH_SIZE = 30;
    private const DEFAULT_INTERVAL_SECONDS = 10;
    private const DEFAULT_TIMEOUT = 0;

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

        $this->log("Consumer initialized for queue: {$queueName}");
    }

    /**
     * Consume messages from queue in batches
     *
     * @param callable $callback Function to process each batch
     * @param int $totalMessages Total number of messages to consume
     * @param int $intervalSeconds Interval in seconds between batch executions
     * @param int $batchSize Number of messages per batch
     * @throws Exception
     */
    public function consume(
        callable $callback,
        int $totalMessages,
        int $intervalSeconds = self::DEFAULT_INTERVAL_SECONDS,
        int $batchSize = self::DEFAULT_BATCH_SIZE
    ): void {
        $batch = [];
        $processedCount = 0;
        $batchNumber = 0;

        $this->log("Starting consumption: {$totalMessages} messages, batch size: {$batchSize}, interval: {$intervalSeconds}s");

        $internalCallback = function ($msg) use (
            &$batch,
            &$processedCount,
            &$batchNumber,
            $callback,
            $batchSize,
            $totalMessages,
            $intervalSeconds
        ): void {
            try {
                $data = json_decode($msg->body, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->log("Error decoding JSON: " . json_last_error_msg(), 'ERROR');
                    $msg->nack(false, false); 
                    return;
                }

                $batch[] = $data;
                $processedCount++;

                if (count($batch) >= $batchSize || $processedCount >= $totalMessages) {
                    $batchNumber++;
                    $this->log("Processing batch #{$batchNumber} with " . count($batch) . " messages");

                    $callback($batch);
                    $batch = [];

                    if ($processedCount < $totalMessages && $intervalSeconds > 0) {
                        $this->log("Waiting {$intervalSeconds} seconds before next batch...");
                        sleep($intervalSeconds);
                    }
                }

                $msg->ack();

                if ($processedCount >= $totalMessages) {
                    $this->log("Reached total messages limit ({$totalMessages}). Stopping consumer.");
                    $this->channel->basic_cancel($msg->getConsumerTag());
                }
            } catch (Exception $e) {
                $this->log("Error processing message: " . $e->getMessage(), 'ERROR');
                $msg->nack(false, true); // Requeue on error
            }
        };

        try {
            $this->channel->basic_qos(null, $batchSize, null);
            $this->channel->basic_consume(
                $this->queueName,
                '',
                false,
                false,
                false,
                false,
                $internalCallback
            );

            while ($this->channel->is_consuming()) {
                try {
                    $this->channel->wait(null, false, self::DEFAULT_TIMEOUT);
                } catch (AMQPTimeoutException $e) {
                    // Timeout is expected, continue
                }
            }

            if (!empty($batch)) {
                $batchNumber++;
                $this->log("Processing final batch #{$batchNumber} with " . count($batch) . " messages");
                $callback($batch);
            }

            $this->log("Consumption completed. Total processed: {$processedCount} messages in {$batchNumber} batches");
        } catch (Exception $e) {
            $this->log("Fatal error in consumer: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
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
            $this->log("Consumer closed successfully");
        } catch (Exception $e) {
            $this->log("Error closing consumer: " . $e->getMessage(), 'ERROR');
        }
    }

    private function log(string $message, string $level = 'INFO'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        echo "[{$timestamp}] [{$level}] {$message}\n";
    }

    public function __destruct()
    {
        $this->close();
    }
}
