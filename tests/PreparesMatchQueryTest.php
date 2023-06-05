<?php

use Drewlabs\Query\Exceptions\MalformedSubQueryException;
use Drewlabs\Query\PreparesMatchQuery;
use PHPUnit\Framework\TestCase;

class PreparesMatchQueryTest extends TestCase
{

    public function test_prepares_match_query_on_dictionnary()
    {
        $result = (new PreparesMatchQuery)->__invoke(['method' => 'where', 'params' => ['likes', '4']]);
        $this->assertEquals('where', $result['method']);
        $this->assertEquals(['likes', 4], $result['params']);
    }

    public function test_prepares_match_query_on_vector()
    {
        $result = (new PreparesMatchQuery)->__invoke(['where', 'likes', '4']);
        $this->assertEquals('where', $result['method']);
        $this->assertEquals(['likes', 4], $result['params']);
    }

    public function test_prepares_match_query_on_incorrect_str_syntax()
    {
        $result = (new PreparesMatchQuery)->__invoke('where(name, like, %computer%)');
        $this->assertTrue(call_user_func('is_array', $result));
        $this->assertEquals('where', $result['method']);
        $this->assertEquals(['name', 'like', '%computer%'], $result['params']);
    }

    public function test_prepares_match_query_on_malformed_str_syntax()
    {
        $this->expectException(MalformedSubQueryException::class);
        (new PreparesMatchQuery)->__invoke('(name, like, \'%computer%\')');
    }

    public function test_prepares_match_query_on_malformed_str_syntax_2()
    {
        $this->expectException(MalformedSubQueryException::class);
        (new PreparesMatchQuery)->__invoke('where(name, like, \'%computer%\'');
    }

    public function test_prepares_match_query_on_invalid_type_syntax()
    {
        $this->expectException(TypeError::class);
        (new PreparesMatchQuery)->__invoke(1);
    }
}
