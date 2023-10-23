<p align="center">
    <a href="https://github.com/yii2-extensions/queue" target="_blank">
        <img src="https://www.yiiframework.com/image/yii_logo_light.svg" height="100px;">
    </a>
    <h1 align="center">Queue</h1>
    <br>
</p>

<p align="center">
    <a href="https://www.php.net/releases/8.1/en.php" target="_blank">
        <img src="https://img.shields.io/badge/PHP-%3E%3D8.1-787CB5" alt="php-version">
    </a>
    <a href="https://github.com/yiisoft/yii2/tree/2.2" target="_blank">
        <img src="https://img.shields.io/badge/Yii2%20version-2.2-blue" alt="yii2-version">
    </a>
    <a href="https://github.com/yii2-extensions/queue/actions/workflows/build.yml" target="_blank">
        <img src="https://github.com/yii2-extensions/queue/actions/workflows/build.yml/badge.svg" alt="PHPUnit">
    </a>
    <a href="https://codecov.io/gh/yii2-extensions/queue" target="_blank">
        <img src="https://codecov.io/gh/yii2-extensions/queue/branch/main/graph/badge.svg?token=MF0XUGVLYC" alt="Codecov">
    </a>
    <a href="https://github.com/yii2-extensions/queue/actions/workflows/static.yml" target="_blank">
        <img src="https://github.com/yii2-extensions/queue/actions/workflows/static.yml/badge.svg" alt="PHPStan">
    </a>
    <a href="https://github.com/yii2-extensions/queue/actions/workflows/static.yml" target="_blank">
        <img src="https://img.shields.io/badge/PHPStan%20level-1-blue" alt="PHPStan level">
    </a>
    <a href="https://github.styleci.io/repos/708447362?branch=main" target="_blank">
        <img src="https://github.styleci.io/repos/708447362/shield?branch=main" alt="Code style">
    </a>        
</p>

An extension for running tasks asynchronously via queues.

It supports queues based on **DB**, **Redis**, **RabbitMQ**, **AMQP**, **Beanstalk**, **ActiveMQ** and **Gearman**.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

## Requirements

- PHP 8.1 or higher.

Installation
------------

The preferred way to install this extension is through [composer](https://getcomposer.org/download/):

```
php composer.phar require --prefer-dist yiisoft/yii2-queue
```

Basic Usage
-----------

Each task which is sent to queue should be defined as a separate class.
For example, if you need to download and save a file the class may look like the following:

```php
class DownloadJob extends BaseObject implements \yii\queue\JobInterface
{
    public $url;
    public $file;
    
    public function execute($queue)
    {
        file_put_contents($this->file, file_get_contents($this->url));
    }
}
```

Here's how to send a task into the queue:

```php
Yii::$app->queue->push(new DownloadJob([
    'url' => 'http://example.com/image.jpg',
    'file' => '/tmp/image.jpg',
]));
```
To push a job into the queue that should run after 5 minutes:

```php
Yii::$app->queue->delay(5 * 60)->push(new DownloadJob([
    'url' => 'http://example.com/image.jpg',
    'file' => '/tmp/image.jpg',
]));
```

The exact way a task is executed depends on the used driver. Most drivers can be run using
console commands, which the component automatically registers in your application.

This command obtains and executes tasks in a loop until the queue is empty:

```sh
yii queue/run
```

This command launches a daemon which infinitely queries the queue:

```sh
yii queue/listen
```

See the documentation for more details about driver specific console commands and their options.

The component also has the ability to track the status of a job which was pushed into queue.

```php
// Push a job into the queue and get a message ID.
$id = Yii::$app->queue->push(new SomeJob());

// Check whether the job is waiting for execution.
Yii::$app->queue->isWaiting($id);

// Check whether a worker got the job from the queue and executes it.
Yii::$app->queue->isReserved($id);

// Check whether a worker has executed the job.
Yii::$app->queue->isDone($id);
```

For more details see [the guide](docs/guide/README.md).
