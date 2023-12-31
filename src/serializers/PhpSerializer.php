<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\queue\serializers;

use yii\base\BaseObject;

/**
 * Php Serializer.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class PhpSerializer extends BaseObject implements SerializerInterface
{
    /**
     * @inheritdoc
     */
    public function serialize($job): string
    {
        return serialize($job);
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        return unserialize($serialized);
    }
}
