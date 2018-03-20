<?php

namespace RabbitMQModule\Interfaces;

use RabbitMQModule\Service\RabbitMQ;

interface ConsumerInterface
{
    public function receive(Callable $callback, RabbitMQ $rabbitMQService);
}
