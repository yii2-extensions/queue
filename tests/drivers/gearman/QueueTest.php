<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace tests\drivers\gearman;

use tests\app\PriorityJob;
use tests\drivers\CliTestCase;
use Yii;
use yii\queue\gearman\Queue;

/**
 * Gearman Queue Test.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class QueueTest extends CliTestCase
{
    public function testRun(): void
    {
        $job = $this->createSimpleJob();
        $this->getQueue()->push($job);
        $this->runProcess(['php', 'yii', 'queue/run']);

        $this->assertSimpleJobDone($job);
    }

    public function testPriority(): void
    {
        $this->getQueue()->priority('high')->push(new PriorityJob(['number' => 1]));
        $this->getQueue()->priority('low')->push(new PriorityJob(['number' => 5]));
        $this->getQueue()->priority('norm')->push(new PriorityJob(['number' => 3]));
        $this->getQueue()->priority('norm')->push(new PriorityJob(['number' => 4]));
        $this->getQueue()->priority('high')->push(new PriorityJob(['number' => 2]));
        $this->runProcess(['php', 'yii', 'queue/run']);

        $this->assertEquals('12345', file_get_contents(PriorityJob::getFileName()));
    }

    public function testStatus(): void
    {
        $job = $this->createSimpleJob();
        $id = $this->getQueue()->push($job);
        $isWaiting = $this->getQueue()->isWaiting($id);
        $this->runProcess(['php', 'yii', 'queue/run']);
        $isDone = $this->getQueue()->isDone($id);

        $this->assertTrue($isWaiting);
        $this->assertTrue($isDone);
    }

    public function testListen(): void
    {
        $this->startProcess(['php', 'yii', 'queue/listen']);
        $job = $this->createSimpleJob();
        $this->getQueue()->push($job);

        $this->assertSimpleJobDone($job);
    }

    /**
     * @return Queue
     */
    protected function getQueue(): Queue
    {
        return Yii::$app->gearmanQueue;
    }

    public function setUp(): void
    {
        if (!defined('GEARMAN_SUCCESS')) {
            $this->markTestSkipped('Gearman in not installed.');
        }

        parent::setUp();
    }
}
