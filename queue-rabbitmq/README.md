# PHP RabbitMQ Queue System

Sistema de filas com RabbitMQ para PHP, com suporte a processamento em lote e intervalos configuráveis.

## 📋 Requisitos

- PHP 7.4 ou superior
- RabbitMQ Server
- Composer

## 🚀 Instalação

```bash
composer require php-amqplib/php-amqplib
```

## ⚙️ Configuração

Copie o arquivo `.env.example` para `.env` e configure as credenciais do RabbitMQ:

```bash
cp .env.example .env
```

Edite o arquivo `.env` com suas configurações:

```env
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
```

## 📖 Uso

### Producer (Produtor)

Envie mensagens para a fila:

```php
use App\Queue\Producer;

$producer = new Producer('logs');

// Enviar mensagem única
$producer->publish([
    'type' => 'user_login',
    'user_id' => 123,
    'timestamp' => time()
]);

// Enviar em lote
$messages = [...]; // Array de mensagens
$producer->publishBatch(
    messages: $messages,
    batchSize: 30,          // 30 mensagens por lote
    intervalSeconds: 2      // 2 segundos entre lotes
);

$producer->close();
```

### Consumer (Consumidor)

Consuma mensagens da fila:

```php
use App\Queue\Consumer;

$consumer = new Consumer('logs');

$callback = function (array $batch): void {
    // Processar o lote de mensagens
    foreach ($batch as $message) {
        echo "Processing: {$message['type']}\n";
    }
};

$consumer->consume(
    callback: $callback,
    totalMessages: 100,      // Total de mensagens a consumir
    intervalSeconds: 10,     // 10 segundos entre lotes
    batchSize: 30           // 30 mensagens por lote
);

$consumer->close();
```

## 📝 Parâmetros

### Producer::publishBatch()

- `messages` (array): Array de mensagens para publicar
- `batchSize` (int): Quantidade de mensagens por lote (padrão: 100)
- `intervalSeconds` (int): Intervalo em segundos entre lotes (padrão: 0)

### Consumer::consume()

- `callback` (callable): Função para processar cada lote
- `totalMessages` (int): Total de mensagens a consumir
- `intervalSeconds` (int): Intervalo em segundos entre execuções (padrão: 10)
- `batchSize` (int): Quantidade de mensagens por lote (padrão: 30)

## 🎯 Exemplos

Execute os exemplos na pasta `examples/`:

```bash
# Produtor
php examples/producer-example.php

# Consumidor
php examples/consumer-example.php
```

## 🔍 Features

- ✅ Processamento em lote configurável
- ✅ Intervalo entre execuções
- ✅ Logging detalhado
- ✅ Tratamento de erros
- ✅ Reconhecimento de mensagens (ACK/NACK)
- ✅ Mensagens persistentes
- ✅ Configuração centralizada
- ✅ Limite de mensagens total

## 📄 Licença

MIT
