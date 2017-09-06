<?php

namespace VladimirYuldashev\LaravelQueueRabbitMQ;

use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Connectors\RabbitMQConnector;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Connectors\RabbitMQConnectorSSL;

class LaravelQueueRabbitMQServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/rabbitmq.php', 'queue.connections.rabbitmq'
        );
    }

    /**
     * Register the application's event listeners.
     *
     * @return void
     */
    public function boot()
    {
        /** @var QueueManager $queue */
        $queue = $this->app['queue'];
        
        if ($this->app['config']['rabbitmq']['ssl_params']['ssl_on'] === true) {
            $connector = new RabbitMQConnectorSSL();
        } else {
            $connector = new RabbitMQConnector();
        }

        $queue->stopping(function () use ($connector) {
            if ($connector->connection() instanceof AMQPStreamConnection) {
                $connector->connection()->close();
            }
        });

        $queue->addConnector('rabbitmq', function () use ($connector) {
            return $connector;
        });
    }
}
