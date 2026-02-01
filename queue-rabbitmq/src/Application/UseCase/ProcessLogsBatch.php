<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Entity\Log\Log;

class ProcessLogsBatch
{
    public function execute(array $batch): void
    {
        foreach ($batch as $logData) {
            $log = Log::fromArray($logData);
            echo "Processando log ID: {$log->getId()} - {$log->getMessage()}\n";
        }
    }
}
