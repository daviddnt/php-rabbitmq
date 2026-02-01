<?php

declare(strict_types=1);

namespace App\Queue;

class RabbitMQConfig
{
    private static ?self $instance = null;

    private string $host;
    private int $port;
    private string $user;
    private string $password;
    private string $vhost;

    private function __construct()
    {
        $this->host = getenv('RABBITMQ_HOST');
        $this->port = (int)(getenv('RABBITMQ_PORT'));
        $this->user = getenv('RABBITMQ_USER');
        $this->password = getenv('RABBITMQ_PASSWORD');
        $this->vhost = getenv('RABBITMQ_VHOST');
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getVhost(): string
    {
        return $this->vhost;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function setPort(int $port): self
    {
        $this->port = $port;
        return $this;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function setVhost(string $vhost): self
    {
        $this->vhost = $vhost;
        return $this;
    }
}
