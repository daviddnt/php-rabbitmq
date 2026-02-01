<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Application\UseCase\ProcessLogsBatch;
use App\Queue\Consumer;

$consumer = new Consumer();
$useCase = new ProcessLogsBatch();

$consumer->consume([$useCase, 'execute'], 30);
