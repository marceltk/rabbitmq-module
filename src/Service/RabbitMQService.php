<?php

namespace RabbitMQModule\Service;

use PhpAmqpLib\Message\AMQPMessage;
use RabbitMQModule\Job\Job;

class RabbitMQService
{

    private static $serviceManager;

    private $queueName = 'rabbitmq.queue.default';
    private $exchangeName = 'rabbitmq.exchange.default';
    private $exchangeType = 'direct';
    private $priority = RabbitMQ::PRIORITY_NORMAL;

    public function __construct($sm)
    {
        self::$serviceManager = $sm;
    }

    public function createChannelWithPriority()
    {
        return $this->createChannel(true);
    }

    public function createChannel($priority = false)
    {
        $rabbitMQService = self::$serviceManager->get(RabbitMQ::class);

        if ($priority == true) {
            $table = $rabbitMQService->getTable();
            $table->set('x-max-priority', RabbitMQ::PRIORITY_SUPER_HIGH);
        }

        $channel = $rabbitMQService->getChannel();

        $channel->queue_declare($this->getQueueName(), false, true, false, false, false, $table);
        $channel->exchange_declare($this->getExchangeName(), $this->getExchangeType(), false, true, false);
        $channel->queue_bind($this->getQueueName(), $this->getExchangeName());

        return $channel;
    }

    public function basicPublish($msgBody, $options)
    {
        if (!isset($options['content_type'])) {
            $options['content_type'] = 'application/json';
        }

        if (isset($options['priority'])) {
            $channel = $this->createChannelWithPriority();
        } else {
            $channel = $this->createChannel(false);
        }

        if (!is_array($msgBody)) {
            $msgBody = ['message' => $msgBody];
        }

        $job = new Job($msgBody);

        $amqpMessage = new AMQPMessage($job->getJsonString(), [
            'delivery_mode' => RabbitMQ::DELIVERY_MODE,
            'priority' => $options['priority'] ? $options['priority'] : $this->getPriority(),
            'content_type' => $options['content_type'],
        ]);

        $channel->basic_publish($amqpMessage, $this->getExchangeName());
    }

    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->queueName;
    }

    /**
     * @param string $queueName
     */
    public function setQueueName($queueName)
    {
        $this->queueName = $queueName;
    }

    /**
     * @return string
     */
    public function getExchangeName()
    {
        return $this->exchangeName;
    }

    /**
     * @param string $exchangeName
     */
    public function setExchangeName($exchangeName)
    {
        $this->exchangeName = $exchangeName;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public function getExchangeType()
    {
        return $this->exchangeType;
    }

    /**
     * @param string $exchangeType
     */
    public function setExchangeType($exchangeType)
    {
        $this->exchangeType = $exchangeType;
    }

}
