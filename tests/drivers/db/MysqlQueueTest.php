<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace tests\drivers\db;

use Yii;
use yii\queue\db\Queue;

/**
 * MySQL Queue Test.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class MysqlQueueTest extends TestCase
{
    /**
     * @return Queue
     */
    protected function getQueue(): Queue
    {
        return Yii::$app->mysqlQueue;
    }
}
