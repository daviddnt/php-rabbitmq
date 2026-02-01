<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Application\UseCase\SendLogsToQueue;
use App\Domain\Entity\Log\Log;

$logs = [];
for ($i = 1; $i <= 1500; $i++) {
    $logs[] = new Log($i, "Mensagem de log $i");
}

$useCase = new SendLogsToQueue();
$useCase->execute($logs);

echo "Logs enviados para a fila.\n";
