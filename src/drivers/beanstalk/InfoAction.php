<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\queue\beanstalk;

use yii\helpers\Console;
use yii\queue\cli\Action;
use yii\queue\cli\Queue as CliQueue;

/**
 * Info about queue status.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class InfoAction extends Action
{
    /**
     * @var Queue
     */
    public CliQueue $queue;

    /**
     * Info about queue status.
     */
    public function run(): void
    {
        Console::output($this->format('Statistical information about the tube:', Console::FG_GREEN));

        foreach ($this->queue->getStatsTube() as $key => $value) {
            Console::stdout($this->format("- $key: ", Console::FG_YELLOW));
            Console::output($value);
        }
    }
}
