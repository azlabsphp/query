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

namespace Drewlabs\Query;

use Drewlabs\Query\Contracts\QueryLanguageInterface;
use InvalidArgumentException;

/**
 * @template TResult
 * 
 * @method TResult                                                   create(array $attributes, \Closure $callback = null)
 * @method TResult                                                   create(array $attributes, $params, bool $batch, \Closure $callback = null)
 * @method TResult                                                   create(array $attributes, $params = [], \Closure $callback)
 * @method bool                                                      delete(int $id)
 * @method bool                                                      delete(string $id)
 * @method int                                                       delete(array $query)
 * @method int                                                       delete(array $query, bool $batch)
 * @method \Drewlabs\Query\Contracts\EnumerableResultInterface|mixed select()
 * @method TResult                                                   select(string $id, array $columns, \Closure $callback = null)
 * @method TResult                                                   select(string $id, \Closure $callback = null)
 * @method TResult                                                   select(int $id, array $columns, \Closure $callback = null)
 * @method TResult                                                   select(int $id, \Closure $callback = null)
 * @method \Drewlabs\Query\Contracts\EnumerableResultInterface|mixed select(array $query, \Closure $callback = null)
 * @method \Drewlabs\Query\Contracts\EnumerableResultInterface|mixed select(array $query, array $columns, \Closure $callback = null)
 * @method mixed                                                     select(array $query, int $per_page, int $page = null, \Closure $callback = null)
 * @method mixed                                                     select(array $query, int $per_page, array $columns, int $page = null, \Closure $callback = null)
 * @method int                                                       aggregate(array $query = [], string $aggregation = \Drewlabs\Query\AggregationMethods::COUNT)
 * @method int                                                       update(array $query, $attributes = [])
 * @method int                                                       update(array $query, $attributes = [], bool $bulkstatement)
 * @method TResult                                                   update(int $id, $attributes, \Closure $dto_transform_fn = null)
 * @method TResult                                                   update(int $id, $attributes, $params, \Closure $dto_transform_fn = null)
 * @method TResult                                                   update(string $id, $attributes, \Closure $dto_transform_fn = null)
 * @method TResult                                                   update(string $id, $attributes, $params, \Closure $dto_transform_fn = null)
 */
final class QueryLanguageAdapter implements QueryLanguageInterface
{
    /**
     * @var QueryLanguageInterface
     */
    private $language;

    /**
     * Creates class instance.
     *
     * @return void
     */
    public function __construct(QueryLanguageInterface $language)
    {
        $this->language = $language;
    }

    public function create(...$args)
    {
        return $this->language->create(...$args);
    }

    public function delete(...$args)
    {
        return $this->language->delete(...$args);
    }

    public function select(...$args)
    {
        return $this->language->select(...$args);
    }

    /**
     * @param mixed ...$args 
     * @return mixed 
     */
    public function update(...$args)
    {
        return $this->language->update(...$args);
    }

    /**
     * insert multiple lines into the database
     * 
     * @param array $values 
     * @return void 
     * @throws InvalidArgumentException 
     */
    public function insert(array $values)
    {
        if (!(array_filter($values, 'is_array') === $values)) {
            throw new \InvalidArgumentException('$attributes must be a multi-dimensional tableau');
        }

        foreach ($values as $value) {
            $this->language->createMany($value);
        }
    }

    /**
     * @deprecated will be remove in future release
     * 
     * @param array $query 
     * @param string $aggregation 
     * @param mixed ...$args 
     * @return int|mixed 
     */
    public function aggregate(array $query = [], string $aggregation = AggregationMethods::COUNT, ...$args)
    {
        // TODO: Provide implementation
    }


    /**
     * @deprecated use insert() instead
     * 
     * @param array $attributes 
     * @return bool 
     */
    public function createMany(array $attributes)
    {
        return $this->insert($attributes);
    }
}
