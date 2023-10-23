<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace tests\drivers\amqp_interop;

use Enqueue\AmqpLib\AmqpConnectionFactory;
use Enqueue\AmqpLib\AmqpContext;
use Interop\Amqp\AmqpDestination;
use Interop\Amqp\Impl\AmqpBind;
use Interop\Amqp\AmqpTopic;
use Interop\Queue\Context;
use Interop\Queue\Exception\Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use tests\drivers\CliTestCase;
use Yii;
use yii\base\InvalidConfigException;
use yii\queue\amqp_interop\Queue;

abstract class AmqpTestCase extends CliTestCase
{
    public ?string $queueName = null;
    public ?string $exchangeName = null;
    public ?string $routingKey = null;
    public string $exchangeType = AmqpTopic::TYPE_DIRECT;
    public int $flags = AmqpDestination::FLAG_DURABLE;

    protected function tearDown(): void
    {
        $this->routingKey = null;
        $this->exchangeName = null;
        $this->queueName = null;

        $this->purgeQueue();

        parent::tearDown();
    }

    /**
     * @param bool $createObject
     *
     * @throws InvalidConfigException
     *
     * @return Queue
     */
    protected function getQueue(bool $createObject = false): Queue
    {
        if ($createObject) {
            /** @var Queue $object */
            return Yii::createObject(array_merge(
                $this->getConnectionConfig(),
                [
                    'class' => Queue::class,
                    'password' => getenv('RABBITMQ_PASSWORD') ?: 'guest',
                    'queueOptionalArguments' => ['x-max-priority' => 10],
                    'queueName' => 'queue-interop',
                    'exchangeName' => 'exchange-interop',
                ]
            ));
        }
        return Yii::$app->amqpInteropQueue;
    }

    /**
     * @throws Exception
     *
     * @return AmqpContext|Context
     */
    protected function getAMQPContext(): Context|AmqpContext
    {
        $factory = new AmqpConnectionFactory(array_merge(
            $this->getConnectionConfig(),
            [
                'pass' => getenv('RABBITMQ_PASSWORD') ?: 'guest',
            ]
        ));
        $context = $factory->createContext();

        $queue = $context->createQueue($this->queueName);
        $queue->addFlag($this->flags);
        $queue->setArguments(['x-max-priority' => 10]);
        $context->declareQueue($queue);

        $topic = $context->createTopic($this->exchangeName);
        $topic->setType($this->exchangeType);
        $topic->addFlag($this->flags);
        $context->declareTopic($topic);

        $context->bind(new AmqpBind($queue, $topic, $this->routingKey));

        return $context;
    }

    private function getAmqpConnection(): AMQPStreamConnection
    {
        return new AMQPStreamConnection(
            getenv('RABBITMQ_HOST') ?: 'localhost',
            getenv('RABBITMQ_PORT') ?: 5672,
            getenv('RABBITMQ_USER') ?: 'guest',
            getenv('RABBITMQ_PASSWORD') ?: 'guest'
        );
    }

    private function purgeQueue(): void
    {
        if (null !== $this->queueName) {
            $connection = $this->getAmqpConnection();
            $channel = $connection->channel();
            $channel->queue_purge($this->queueName, true);
        }
    }

    private function getConnectionConfig(): array
    {
        return [
            'host' => getenv('RABBITMQ_HOST') ?: 'localhost',
            'user' => getenv('RABBITMQ_USER') ?: 'guest',
            'port' => getenv('RABBITMQ_PORT') ?: 5672,
        ];
    }
}
