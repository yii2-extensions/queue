<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    public function testExample(): void
    {
        $example = new Example();

        $this->assertTrue($example->getExample());
    }
}
