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
 * Exec Event.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class ExecEvent extends JobEvent
{
    /**
     * @var int attempt number.
     *
     * @see Queue::EVENT_BEFORE_EXEC
     * @see Queue::EVENT_AFTER_EXEC
     * @see Queue::EVENT_AFTER_ERROR
     */
    public int $attempt;
    /**
     * @var mixed result of a job execution in case job is done.
     *
     * @see Queue::EVENT_AFTER_EXEC
     * @since 2.1.1
     */
    public $result;
    /**
     * @var \Exception|\Throwable|null
     *
     * @see Queue::EVENT_AFTER_ERROR
     * @since 2.1.1
     */
    public $error;
    /**
     * @var bool|null
     *
     * @see Queue::EVENT_AFTER_ERROR
     * @since 2.1.1
     */
    public $retry;
}
