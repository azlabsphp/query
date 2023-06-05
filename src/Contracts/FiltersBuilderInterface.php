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

namespace Drewlabs\Query\Contracts;

interface FiltersBuilderInterface
{
    /**
     * add `and` query.
     *
     * @param string|\Closure $column
     * @param mixed|null      $value
     *
     * @return self
     */
    public function and($column, string $operator = null, $value = null);

    /**
     * apply `date` query.
     *
     * @param string                    $column
     * @param string                    $operator
     * @param \DateTimeInterface|string $value
     *
     * @return mixed
     */
    public function date($column, string $operator = null, $value = null);

    /**
     * apply `or date` query.
     *
     * @param string                    $column
     * @param string                    $operator
     * @param \DateTimeInterface|string $value
     *
     * @return mixed
     */
    public function orDate($column, string $operator = null, $value = null);

    /**
     * add `or` query.
     *
     * @param string|\Closure $column
     * @param mixed|null      $value
     *
     * @return self
     */
    public function or($column, string $operator = null, $value = null);

    /**
     * add `exists` query.
     *
     * @param string|\Closure(self $builder):self $query
     *
     * @return self
     */
    public function exists(string $column, $query = null);

    /**
     * add `or exists` query.
     *
     * @param string|\Closure(self $builder):self $query
     *
     * @return self
     */
    public function orExists(string $column, $query = null);

    /**
     * add `not exists` query.
     *
     * @param string|\Closure(self $builder):self $query
     *
     * @return self
     */
    public function notExists(string $column, $query = null);

    /**
     * add `or not exists` query.
     *
     * @param string|\Closure(self $builder):self $query
     *
     * @return self
     */
    public function orNotExists(string $column, $query = null);

    /**
     * add `in` query.
     *
     * @return self
     */
    public function in(string $column, array $values);

    /**
     * add `not in query`.
     *
     * @return self
     */
    public function notIn(string $column, array $values);

    /**
     * add `sort` query.
     *
     * @return self
     */
    public function sort(string $column, int $order = 1);

    /**
     * add `is null` query.
     *
     * @return self
     */
    public function isNull(string $column);

    /**
     * add `or is null` query.
     *
     * @return self
     */
    public function orIsNull(string $column);

    /**
     * add `not null` query.
     *
     * @return self
     */
    public function notNull(string $column);

    /**
     * add `or not null` query.
     *
     * @return self
     */
    public function orNotNull(string $column);

    /**
     * add `between` query.
     *
     * @param iterable $values
     *
     * @return self
     */
    public function between(string $column, $values);

    /**
     * add `group by` query.
     *
     * @return self
     */
    public function group(string $column);

    /**
     * add `join` query.
     *
     * @return self
     */
    public function join(string $table, string $first, string $operator, string $second);

    /**
     * add `limit` query.
     *
     * @return self
     */
    public function limit(int $limit);

    /**
     * Get the builded query filters.
     *
     * **Note**
     * If an argument is passed in the implementation
     * must be able to resolve specific filter value.
     *
     * @return array|mixed
     */
    public function getQuery(string $method = null);
}
