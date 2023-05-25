<?php

use Drewlabs\Query\Columns;
use PHPUnit\Framework\TestCase;

class ColumnsTest extends TestCase
{

    public function test_tuple_method()
    {
        $columns = Columns::new(['title', 'contents', 'comments.ratings']);
        $this->assertEquals([['title', 'contents'], ['comments.ratings']], $columns->tuple(['title', 'contents'], ['comments']));
    }

    public function test_tuple_method_for_multi_dimensional_array()
    {
        $columns = Columns::new(['title', ['contents', ['comments.ratings']]]);
        $this->assertEquals([['title', 'contents'], ['comments.ratings']], $columns->tuple(['title', 'contents'], ['comments']));
    }
}