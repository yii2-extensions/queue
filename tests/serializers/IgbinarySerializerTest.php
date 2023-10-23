<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace tests\serializers;

use yii\queue\serializers\IgbinarySerializer;

/**
 * Igbinary Serializer Test.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class IgbinarySerializerTest extends TestCase
{
    protected function createSerializer(): IgbinarySerializer
    {
        return new IgbinarySerializer();
    }

    protected function setUp(): void
    {
        if (!extension_loaded('igbinary')) {
            $this->markTestSkipped('Igbinary extension is not loaded.');
        }

        parent::setUp();
    }
}
