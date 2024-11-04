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

use Drewlabs\Query\Columns;
use PHPUnit\Framework\TestCase;

class ColumnsTest extends TestCase
{
    public function test_tuple_method()
    {
        $columns = Columns::new(['title', 'contents', 'comments.ratings']);
        $this->assertSame([['title', 'contents'], ['comments.ratings']], $columns->tuple(['title', 'contents'], ['comments']));
    }

    public function test_tuple_method_for_multi_dimensional_array()
    {
        $columns = Columns::new(['title', ['contents', ['comments.ratings']]]);
        $this->assertSame([['title', 'contents'], ['comments.ratings']], $columns->tuple(['title', 'contents'], ['comments']));
    }
}
