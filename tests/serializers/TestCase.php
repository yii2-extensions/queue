<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace tests\serializers;

use tests\app\SimpleJob;
use tests\TestCase as BaseTestCase;
use yii\base\BaseObject;
use yii\queue\serializers\SerializerInterface;

/**
 * Serializer Test Case.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @return SerializerInterface
     */
    abstract protected function createSerializer(): SerializerInterface;

    /**
     * @dataProvider providerSerialize
     *
     * @param mixed $expected
     */
    public function testSerialize($expected): void
    {
        $serializer = $this->createSerializer();

        $serialized = $serializer->serialize($expected);
        $actual = $serializer->unserialize($serialized);

        $this->assertEquals($expected, $actual, "Payload: $serialized");
    }

    public static function providerSerialize(): array
    {
        return [
            // Job object
            [
                new SimpleJob(['uid' => 123]),
            ],
            // Any object
            [
                new TestObject([
                    'foo' => 1,
                    'bar' => [
                        new TestObject(['foo' => 1]),
                    ],
                ]),
            ],
            // Array of mixed data
            [
                [
                    'a' => 'b',
                    'c' => [
                        222,
                        new TestObject(),
                    ],
                    'd' => [
                        new TestObject(),
                    ],
                ],
            ],
            // Scalar
            [
                'string value',
            ],
        ];
    }
}

class TestObject extends BaseObject
{
    public $foo;
    public $bar;
}
