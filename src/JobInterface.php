<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\queue;

/**
 * Job Interface.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
interface JobInterface
{
    /**
     * @param Queue $queue which pushed and is handling the job
     *
     * @return mixed|void result of the job execution
     */
    public function execute(Queue $queue);
}
