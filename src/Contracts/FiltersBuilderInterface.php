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
     * @param string|\DateTimeInterface|array $date
     *
     * @return self
     */
    public function date($date);

    /**
     * add `or date` query.
     *
     * @param string|\DateTimeInterface $date
     *
     * @return self
     */
    public function orDate($date);

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
     * @return self
     */
    public function exists(string $column, callable $callback = null);

    /**
     * add `not exists` query.
     *
     * @return self
     */
    public function notExists(string $column, callable $callback = null);

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
     * @param int $order
     *
     * @return self
     */
    public function sort(string $column, $order = 1);

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
     * @param string|\DateTimeInterface $start
     * @param string|\DateTimeInterface $end
     *
     * @return self
     */
    public function between($start, $end);

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
}
