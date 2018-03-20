<?php

namespace RabbitMQModule\Interfaces;

use RabbitMQModule\Service\RabbitMQ;

interface PublisherInterface
{
    public function push(JobInterface $job, RabbitMQ $rabbitMQService);
}
