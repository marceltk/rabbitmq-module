<?php

return [
    'service_manager' => [

        'factories' => [
            \RabbitMQModule\Service\RabbitMQ::class => function (\Interop\Container\ContainerInterface $container) {
                $config = $container->get('config');

                return new \RabbitMQModule\Service\RabbitMQ($config['rabbitmq']);
            },
            \RabbitMQModule\Service\RabbitMQService::class => function ($sm) {
                return new \RabbitMQModule\Service\RabbitMQService($sm);
            },
        ],

        'aliases' => [
            'rabbitmq.service' => \RabbitMQModule\Service\RabbitMQService::class,
            'rabbitmq' => \RabbitMQModule\Service\RabbitMQ::class,
        ],

    ],
];