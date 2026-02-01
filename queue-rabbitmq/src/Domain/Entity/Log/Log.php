<?php

declare(strict_types=1);

namespace App\Domain\Entity\Log;

class Log
{
    private int $id;
    private string $message;

    public function __construct(int $id, string $message)
    {
        $this->id = $id;
        $this->message = $message;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self($data['id'], $data['message']);
    }
}
