<?php

namespace Drewlabs\Query\Tests;

use Drewlabs\Query\Exceptions\MalformedQueryExpression;
use Drewlabs\Query\PreparesQueryStatement;
use Drewlabs\Query\QueryStatement;
use PHPUnit\Framework\TestCase;

class PreparesMatchQueryTest extends TestCase
{

    public function test_prepares_match_query_on_dictionnary()
    {
        $result = (new PreparesQueryStatement)->__invoke(['method' => 'where', 'params' => ['likes', '4']])[0];
        $this->assertEquals('where', $result->method());
        $this->assertEquals(['likes', 4], $result->args());
    }

    public function test_prepares_match_query_on_vector()
    {
        $result = (new PreparesQueryStatement)->__invoke(['where', 'likes', '4'])[0];
        $this->assertEquals('where', $result->method());
        $this->assertEquals(['likes', 4], $result->args());
    }

    public function test_prepares_match_query_on_incorrect_str_syntax()
    {
        $result = (new PreparesQueryStatement)->__invoke('where(name, like, %computer%)')[0];
        $this->assertInstanceOf(QueryStatement::class, $result);
        $this->assertEquals('where', $result->method());
        $this->assertEquals(['name', 'like', '%computer%'], $result->args());
    }

    public function test_prepares_match_query_on_malformed_str_syntax()
    {
        $this->expectException(MalformedQueryExpression::class);
        (new PreparesQueryStatement)->__invoke('(name, like, \'%computer%\')');
    }

    public function test_prepares_match_query_on_malformed_str_syntax_2()
    {
        $this->expectException(MalformedQueryExpression::class);
        (new PreparesQueryStatement)->__invoke('where(name, like, \'%computer%\'');
    }

    public function test_prepares_match_query_on_invalid_type_syntax()
    {
        $this->expectException(\TypeError::class);
        (new PreparesQueryStatement)->__invoke(1);
    }
}
