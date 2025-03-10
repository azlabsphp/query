<?php

namespace Drewlabs\Query\Tests;

use Drewlabs\Query\Builder as QueryBuilder;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{

    public function test_builder_builder_where_clause()
    {
        $builder = QueryBuilder::new()
            ->eq('title', 'Lorem Ipsum')
            ->neq('id', 10)
            ->and(function (QueryBuilder $builder) {
                return $builder->in('tags', ['I', 'L', 'F'])
                    ->gt('likes', 120)
                    ->lte('rates', 4.5)
                    ->lt('comments', 1200)
                    ->gte('groups', 10);
            });

        $rawQuery = $builder->getRawQuery('and') ?? [];
        $this->assertEquals(['title', '=', 'Lorem Ipsum'], $rawQuery[0]);
        $this->assertEquals(['id', '<>', 10], $rawQuery[1]);
        $this->assertEquals('and', $rawQuery[2]['method']);
        $this->assertEquals(['tags', ['I', 'L', 'F']], $rawQuery[2]['params']['in'][0]);
    }

    public function test_builder_or_clause()
    {
        $builder = QueryBuilder::new()
            ->and('title', 'Lorem Ipsum')
            ->or('id', 10);

        $result = $builder->getQuery('and') ?? [];
        $orResult = $builder->getQuery('or') ?? [];
        $this->assertEquals(['title', '=', 'Lorem Ipsum'], $result[0]);
        $this->assertEquals(['id', '=', 10], $orResult[0]);
    }

    public function test_builder_not_clause()
    {
        $builder = QueryBuilder::new()->or('id', 10)->neq('likes', 4);
        $result = $builder->getQuery('and') ?? [];
        $orResult = $builder->getQuery('or') ?? [];
        $this->assertEquals(['likes', '<>', 4], $result[0]);
        $this->assertEquals(['id', '=', 10], $orResult[0]);
    }

    public function test_builder_in_clause()
    {
        $builder = QueryBuilder::new()->in('likes', [5, 10])->notIn('id', [90, 120, 167]);
        $result = $builder->getQuery('in') ?? [];
        $this->assertEquals(['likes', [5, 10]], $result[0]);
        $notInQuery = $builder->getQuery('notIn');
        $this->assertEquals(['id', [90, 120, 167]], $notInQuery[0]);
    }

    public function test_builder_exists_clause()
    {
        $builder = QueryBuilder::new()->exists('comments', function (QueryBuilder $query) {
            return $query->gte('likes', 100);
        });
        $query = $builder->getQuery();
        $this->assertTrue(array_key_exists('exists', $query));
        $this->assertEquals('comments', $query['exists'][0][0]);
        $this->assertInstanceOf(\Closure::class, $query['exists'][0][1]);

        $rawQuery = $builder->getRawQuery();
        $this->assertEquals(['likes', '>=', 100], $rawQuery['exists'][0]['match']['params']['and'][0]);
    }

    public function test_builder_sort_clause()
    {
        $builder = QueryBuilder::new()->sort('created_at', -1);
        $result = $builder->getQuery('sort') ?? [];
        $this->assertEquals(['order' => 'desc', 'by' => 'created_at'], $result);
    }

    public function test_builder_distinct_clause()
    {
        $builder = QueryBuilder::new()->select(['*'])->distinct();
        $this->assertEquals(true, $builder->getQuery('distinct'));
        $builder->distinct('name');
        $this->assertEquals(['name'], $builder->getQuery('distinct'));
    }


    public function test_when_callback_is_called_if_first_parameter_evalutes_to_true()
    {

        $builder = QueryBuilder::new()->when(true, function ($q) {
            return $q->notIn('id', [90, 120, 167]);
        })
            ->and('title', 'Lorem Ipsum');


        $this->assertEquals(['id', [90, 120, 167]], $builder->getQuery('notIn')[0]);
    }

    public function test_when_callback_is_not_called_if_parameter_evaluates_to_false()
    {
        $builder = QueryBuilder::new()->when(false, function ($q) {
            return $q->notIn('id', [90, 120, 167]);
        })
            ->and('title', 'Lorem Ipsum');


        $this->assertEquals([], $builder->getQuery('notIn', []));
    }
}
