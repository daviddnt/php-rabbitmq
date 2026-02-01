<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Domain\Entity\Log\Log as EntityLog;
use App\Queue\Producer;

$producer = new Producer();

$batchSize = 100; 
$delayInSeconds = 30;
$batch = []; 

for ($i = 1; $i <= 15000; $i++) {
    $log = new EntityLog($i, "Mensagem de log $i");
    $batch[] = $log->toArray();

    if (count($batch) >= $batchSize) {
        $producer->publishBatch($batch, $batchSize, $delayInSeconds);
        echo "Lote de $batchSize mensagens enviado.\n";
        $batch = [];
    }
}


if (!empty($batch)) {
    $producer->publishBatch($batch, count($batch), $delayInSeconds);
    echo "Lote final com " . count($batch) . " mensagens enviado.\n";
}

$producer->close();