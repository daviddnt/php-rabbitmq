<?php

declare(strict_types=1);

namespace App\Application\Actions\Log;

use App\Application\Actions\Action;
use App\Application\UseCase\SendLogsToQueue;
use App\Domain\Entity\Log\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class SendLogsAction extends Action
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    protected function action(): ResponseInterface
    {
        $logs = [];
        for ($i = 1; $i <= 1500; $i++) {
            $logs[] = new Log($i, "Mensagem de log $i");
        }

        $useCase = new SendLogsToQueue();
        $useCase->execute($logs);

        $this->response->getBody()->write(json_encode(['status' => 'Logs enviados para a fila']));

        return $this->response->withHeader('Content-Type', 'application/json');
    }
}
