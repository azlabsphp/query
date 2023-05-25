<?php

declare(strict_types=1);

/*
 * This file is part of the drewlabs namespace.
 *
 * (c) Sidoine Azandrew <azandrewdevelopper@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Drewlabs\Query\Tests;

use Drewlabs\Query\Attribute;
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
    public function test_contructor()
    {
        $object = new Attribute([
            'model' => Person::class,
            'column' => 'firstname',
        ]);
        $this->assertInstanceOf(Attribute::class, $object);
    }

    public function test_constructor_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Attribute([
            'model' => null,
            'column' => 'firstname',
        ]);
    }
}
