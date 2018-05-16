<?php

namespace RabbitMQModule\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use RabbitMQModule\Interfaces\ConsumerInterface;
use RabbitMQModule\Service\RabbitMQ;

class WorkQueueConsumer implements ConsumerInterface
{
    protected $queueName;
    protected $config;

    public function __construct($queueName)
    {
        $this->queueName = $queueName;
    }

    public function receive(Callable $callback, RabbitMQ $rabbitMQService)
    {
        $config = $rabbitMQService->getConfig()['queues'][$this->queueName];
        $channel = $rabbitMQService->getChannel();

        $channel->queue_declare($this->queueName, false, true, false, false, false, $config['properties']);
        $channel->basic_qos(null, 1, null);

        $channel->basic_consume($this->queueName, '', false, false, false, false, function (AMQPMessage $msg) use ($callback) {
            $message = new Message($msg);

            $callback($message);
        });

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
}
