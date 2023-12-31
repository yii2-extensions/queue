<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\queue\db;

use yii\console\Exception;
use yii\queue\cli\Command as CliCommand;
use yii\queue\cli\Queue as CliQueue;

/**
 * Manages application db-queue.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class Command extends CliCommand
{
    /**
     * @var Queue
     */
    public CliQueue $queue;
    /**
     * @var string
     */
    public $defaultAction = 'info';

    /**
     * @inheritdoc
     */
    public function actions(): array
    {
        return [
            'info' => InfoAction::class,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function isWorkerAction($actionID): bool
    {
        return in_array($actionID, ['run', 'listen'], true);
    }

    /**
     * Runs all jobs from db-queue.
     * It can be used as cron job.
     *
     * @return int|null exit code.
     */
    public function actionRun(): ?int
    {
        return $this->queue->run(false);
    }

    /**
     * Listens db-queue and runs new jobs.
     * It can be used as daemon process.
     *
     * @param int $timeout number of seconds to sleep before next reading of the queue.
     *
     *@throws Exception when params are invalid.
     *
     * @return int|null exit code.
     */
    public function actionListen(int $timeout = 3): ?int
    {
        if ($timeout < 1) {
            throw new Exception('Timeout must be greater than zero.');
        }

        return $this->queue->run(true, $timeout);
    }

    /**
     * Clears the queue.
     *
     * @since 2.0.1
     */
    public function actionClear(): void
    {
        if ($this->confirm('Are you sure?')) {
            $this->queue->clear();
        }
    }

    /**
     * Removes a job by id.
     *
     * @param int $id
     *
     * @throws Exception when the job is not found.
     *
     * @since 2.0.1
     */
    public function actionRemove(int $id): void
    {
        if (!$this->queue->remove($id)) {
            throw new Exception('The job is not found.');
        }
    }
}
