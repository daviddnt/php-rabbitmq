<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Queue\Producer;

class SendLogsToQueue
{
    public function execute(array $logs): void
    {
        $producer = new Producer();
        foreach ($logs as $log) {
            $producer->publish($log);
        }
        $producer->close();
    }
}
