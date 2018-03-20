<?php

namespace RabbitMQModule\Publisher;

use PhpAmqpLib\Message\AMQPMessage;
use RabbitMQModule\Interfaces\JobInterface;
use RabbitMQModule\Interfaces\PublisherInterface;
use RabbitMQModule\Service\RabbitMQ;

class WorkQueuePublisher implements PublisherInterface
{
    protected $queueName;

    public function __construct($queueName)
    {
        $this->queueName = $queueName;
    }

    public function push(JobInterface $job, RabbitMQ $rabbitMQService)
    {
        $table = $rabbitMQService->getTable();
        $channel = $rabbitMQService->getChannel();

        $channel->queue_declare($this->queueName, false, true, false, false, false, $table);

        $amqpMessage = new AMQPMessage($job->getJsonString(), [
            'delivery_mode' => RabbitMQ::DELIVERY_MODE,
        ]);

        $channel->basic_publish($amqpMessage, '', $this->queueName);
    }
}
