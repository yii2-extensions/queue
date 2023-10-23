<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\queue\cli;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RuntimeException as ProcessRuntimeException;
use Symfony\Component\Process\Process;
use yii\console\Controller;
use yii\queue\ExecEvent;

/**
 * Base Command.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
abstract class Command extends Controller
{
    /**
     * The exit code of the exec action which is returned when job was done.
     */
    public const EXEC_DONE = 0;
    /**
     * The exit code of the exec action which is returned when job wasn't done and wanted next attempt.
     */
    public const EXEC_RETRY = 3;

    /**
     * @var Queue
     */
    public Queue $queue;
    /**
     * @var bool verbose mode of a job execute. If enabled, execute result of each job
     * will be printed.
     */
    public bool $verbose = false;
    /**
     * @var array additional options to the verbose behavior.
     *
     * @since 2.0.2
     */
    public array $verboseConfig = [
        'class' => VerboseBehavior::class,
    ];
    /**
     * @var bool isolate mode. It executes a job in a child process.
     */
    public bool $isolate = true;
    /**
     * @var string|null path to php interpreter that uses to run child processes.
     * If it is undefined, PHP_BINARY will be used.
     *
     * @since 2.0.3
     */
    public ?string $phpBinary = null;

    /**
     * @inheritdoc
     */
    public function options($actionID): array
    {
        $options = parent::options($actionID);
        if ($this->canVerbose($actionID)) {
            $options[] = 'verbose';
        }
        if ($this->canIsolate($actionID)) {
            $options[] = 'isolate';
            $options[] = 'phpBinary';
        }

        return $options;
    }

    /**
     * @inheritdoc
     */
    public function optionAliases(): array
    {
        return array_merge(parent::optionAliases(), [
            'v' => 'verbose',
        ]);
    }

    /**
     * @param string $actionID
     *
     * @return bool
     *
     * @since 2.0.2
     */
    abstract protected function isWorkerAction(string $actionID): bool;

    /**
     * @param string $actionID
     *
     * @return bool
     */
    protected function canVerbose(string $actionID): bool
    {
        return $actionID === 'exec' || $this->isWorkerAction($actionID);
    }

    /**
     * @param string $actionID
     *
     * @return bool
     */
    protected function canIsolate(string $actionID): bool
    {
        return $this->isWorkerAction($actionID);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        if ($this->canVerbose($action->id) && $this->verbose) {
            $this->queue->attachBehavior('verbose', ['command' => $this] + $this->verboseConfig);
        }

        if ($this->canIsolate($action->id) && $this->isolate) {
            if ($this->phpBinary === null) {
                $this->phpBinary = PHP_BINARY;
            }
            $this->queue->messageHandler = function ($id, $message, $ttr, $attempt) {
                return $this->handleMessage($id, $message, (int)$ttr, (int)$attempt);
            };
        }

        return parent::beforeAction($action);
    }

    /**
     * Executes a job.
     * The command is internal, and used to isolate a job execution. Manual usage is not provided.
     *
     * @param string|null $id of a message
     * @param int $ttr time to reserve
     * @param int $attempt number
     * @param int $pid of a worker
     *
     * @return int exit code
     *
     * @internal It is used with isolate mode.
     */
    public function actionExec(?string $id, int $ttr, int $attempt, int $pid): int
    {
        if ($this->queue->execute($id, file_get_contents('php://stdin'), $ttr, $attempt, $pid ?: null)) {
            return self::EXEC_DONE;
        }
        return self::EXEC_RETRY;
    }

    /**
     * Handles message using child process.
     *
     * @param int|string|null $id of a message
     * @param string $message
     * @param int|null $ttr time to reserve
     * @param int $attempt number
     *
     * @return bool
     *
     * @see actionExec()
     */
    protected function handleMessage(int|string|null $id, string $message, ?int $ttr, int $attempt): bool
    {
        // Child process command: php yii queue/exec "id" "ttr" "attempt" "pid"
        $cmd = [
            $this->phpBinary,
            $_SERVER['SCRIPT_FILENAME'],
            $this->uniqueId . '/exec',
            $id,
            $ttr,
            $attempt,
            $this->queue->getWorkerPid() ?: 0,
        ];

        foreach ($this->getPassedOptions() as $name) {
            if (in_array($name, $this->options('exec'), true)) {
                $cmd[] = '--' . $name . '=' . $this->$name;
            }
        }
        if (!in_array('color', $this->getPassedOptions(), true)) {
            $cmd[] = '--color=' . $this->isColorEnabled();
        }

        $process = new Process($cmd, null, null, $message, $ttr);
        try {
            $result = $process->run(function ($type, $buffer) {
                if ($type === Process::ERR) {
                    $this->stderr($buffer);
                } else {
                    $this->stdout($buffer);
                }
            });
            if (!in_array($result, [self::EXEC_DONE, self::EXEC_RETRY])) {
                throw new ProcessFailedException($process);
            }
            return $result === self::EXEC_DONE;
        } catch (ProcessRuntimeException $error) {
            [$job] = $this->queue->unserializeMessage($message);
            return $this->queue->handleError(new ExecEvent([
                'id' => $id,
                'job' => $job,
                'ttr' => $ttr,
                'attempt' => $attempt,
                'error' => $error,
            ]));
        }
    }
}
