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

use Closure;
use Drewlabs\Query\Contracts\FiltersBuilderInterface;
use Drewlabs\Query\Utils\SubQuery;
use Drewlabs\Query\Contracts\Conditionable;

/**
 * Provides implementation for building filters query using fluent interface.
 *
 * @author Sidoine Azandrew <contact@liksoft.tg>
 * 
 * @mixin Conditionable
 */
final class Builder implements FiltersBuilderInterface
{
    /**
     * REST query value.
     *
     * @var array
     */
    private $__QUERY__ = [];

    /**
     * List of column to include in the query result.
     *
     * @var array
     */
    private $__COLUMNS__ = [];

    /**
     * List of column to exclude from the query result.
     *
     * @var array
     */
    private $__EXCLUDES__ = [];

    /**
     * Class instance factory method.
     *
     * @return static
     */
    public static function new()
    {
        return new static();
    }

    public function and($column, ?string $operator = null, $value = null)
    {
        $column = $column instanceof \Closure ? new SubQuery('and', $column(static::new())->getQuery()) : $column;
        $this->setWhereQuery('and', $column, $operator, $value);

        return $this;
    }

    /**
     * Add an or query filter to the builder.
     *
     * @param string|SubQuery|\Closure(self $builder):self $column
     * @param string|null                                  $operator
     * @param mixed|null                                   $value
     *
     * @return static
     */
    public function or($column, $operator = null, $value = null)
    {
        $column = $column instanceof \Closure ? new SubQuery('and', $column(static::new())->getQuery()) : $column;
        $this->setWhereQuery('or', $column, $operator, $value);

        return $this;
    }

    /**
     * Add a `equals` clause to the query builder.
     *
     * @param mixed $value
     * @param bool  $and   Specify if an or/and query is constructed
     *
     * @return static
     */
    public function eq(string $column, $value = null, $and = true)
    {
        return $and ? $this->and($column, '=', $value) : $this->or($column, '=', $value);
    }

    /**
     * Add a `not equals` clause to the query builder.
     *
     * @param mixed $value
     * @param bool  $and   Specify if an or/and query is constructed
     *
     * @return static
     */
    public function neq(string $column, $value = null, $and = true)
    {
        return $and ? $this->and($column, '<>', $value) : $this->or($column, '<>', $value);
    }

    /**
     * Add a `less than` clause to the query builder.
     *
     * @param mixed $value
     * @param bool  $and   Specify if an or/and query is constructed
     *
     * @return static
     */
    public function lt(string $column, $value = null, $and = true)
    {
        return $and ? $this->and($column, '<', $value) : $this->or($column, '<', $value);
    }

    /**
     * Add a `less than or equal to` clause to the query builder.
     *
     * @param mixed $value
     * @param bool  $and   Specify if an or/and query is constructed
     *
     * @return static
     */
    public function lte(string $column, $value = null, $and = true)
    {
        return $and ? $this->and($column, '<=', $value) : $this->or($column, '<=', $value);
    }

    /**
     * Add a `greater than` clause to the query builder.
     *
     * @param mixed $value
     * @param bool  $and   Specify if an or/where query is constructed
     *
     * @return static
     */
    public function gt(string $column, $value = null, $and = true)
    {
        return $and ? $this->and($column, '>', $value) : $this->or($column, '>', $value);
    }

    /**
     * Add a `greater than or equal to` clause to the query builder.
     *
     * @param mixed $value
     * @param bool  $and   Specify if an or/where query is constructed
     *
     * @return static
     */
    public function gte(string $column, $value = null, $and = true)
    {
        return $and ? $this->and($column, '>=', $value) : $this->or($column, '>=', $value);
    }

    /**
     * Add a `like or match` clause to the builder.
     *
     * @param mixed $value
     * @param bool  $and   Specify if an or/where query is constructed
     *
     * @return static
     */
    public function like(string $column, $value = null, $and = true)
    {
        return $and ? $this->and($column, 'like', $value) : $this->or($column, 'like', $value);
    }

    public function date($column, ?string $operator = null, $value = null)
    {
        $this->setWhereQuery('date', $column, $operator, $value);

        return $this;
    }

    public function orDate($column, ?string $operator = null, $value = null)
    {
        $this->setWhereQuery('orDate', $column, $operator, $value);

        return $this;
    }

    public function in(string $column, array $values)
    {
        $this->appendQuery(Filters::get(__FUNCTION__), [$column, $values]);

        return $this;
    }

    public function notIn(string $column, array $values)
    {
        $this->appendQuery(Filters::get(__FUNCTION__), [$column, $values]);

        return $this;
    }

    public function exists(string $as, $query = null)
    {
        $this->setExistQuery($as, $query);

        return $this;
    }

    public function orExists(string $column, $query = null)
    {
        $this->setExistQuery($column, $query, 'orExists');

        return $this;
    }

    public function notExists(string $column, $query = null)
    {
        $this->setExistQuery($column, $query, 'notExists');

        return $this;
    }

    public function orNotExists(string $column, $query = null)
    {
        $this->setExistQuery($column, $query, 'orNotExists');

        return $this;
    }

    public function sort(string $column, int $order = 1)
    {
        $this->__QUERY__ = $this->__QUERY__ ?? [];
        $orderstr = (int) $order < 0 ? 'desc' : 'asc';
        $this->__QUERY__['sort'] = ['order' => $orderstr, 'by' => $column];

        return $this;
    }

    /**
     * Instruct the query builder to append a count attribute
     * named after `$as` variable to the query result.
     *
     * @param string $column
     * @param string $relation
     *
     * @return static
     */
    public function count($column = '*', ?string $relation = null)
    {
        $this->appendQuery(__FUNCTION__, null !== $relation ? [$column, $relation] : [$column]);

        return $this;
    }

    /**
     * Compute the minimum of a given column
     * 
     * @param string $column 
     * @param string|null $relation 
     * @return static
     */
    public function min(string $column, ?string $relation = null)
    {
        $this->appendQuery(__FUNCTION__, null !== $relation ? [$column, $relation] : [$column]);

        return $this;
    }

    /**
     * Compute the maximum of a given column
     * 
     * @param string $column 
     * @param string|null $relation 
     * @return static
     */
    public function max(string $column, ?string $relation = null)
    {
        $this->appendQuery(__FUNCTION__, null !== $relation ? [$column, $relation] : [$column]);

        return $this;
    }

    /**
     * Compute the sum of a given column
     * 
     * @param string $column 
     * @param string|null $relation 
     * @return static
     */
    public function sum(string $column, ?string $relation = null)
    {
        $this->appendQuery(__FUNCTION__, null !== $relation ? [$column, $relation] : [$column]);

        return $this;
    }

    /**
     * Compute the average of a given column
     * 
     * @param string $column 
     * @param string|null $relation 
     * @return static
     */
    public function avg(string $column, ?string $relation = null)
    {
        $this->appendQuery(__FUNCTION__, null !== $relation ? [$column, $relation] : [$column]);

        return $this;
    }

    public function isNull(string $column)
    {
        $this->appendQuery(Filters::get(__FUNCTION__), $column);

        return $this;
    }

    public function orIsNull(string $column)
    {
        $this->appendQuery(Filters::get(__FUNCTION__), $column);

        return $this;
    }

    public function notNull(string $column)
    {
        $this->appendQuery(Filters::get(__FUNCTION__), $column);

        return $this;
    }

    public function orNotNull(string $column)
    {
        $this->appendQuery(Filters::get(__FUNCTION__), $column);

        return $this;
    }

    public function between(string $column, $values)
    {
        $this->appendQuery(Filters::get(__FUNCTION__), [$column, $values]);

        return $this;
    }

    public function group(string $column)
    {
        $this->appendQuery(Filters::get(__FUNCTION__), $column);

        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second)
    {
        throw new \RuntimeException('Method not implemented');
    }

    public function limit(int $limit)
    {
        $this->__QUERY__[Filters::get(__FUNCTION__)] = $limit;

        return $this;
    }

    /**
     * Set the list of columns to include in the rest query result.
     *
     * @param mixed $columns
     *
     * @return static
     */
    public function select(...$columns)
    {
        // code...
        $this->__COLUMNS__ = array_unique(array_merge($this->__COLUMNS__ ?? [], $this->flatten($columns)));

        return $this;
    }

    /**
     * Add a distinct clause to the query builder
     * 
     * @param mixed $columns 
     * @return static 
     */
    public function distinct(...$columns)
    {
        if (empty($columns)) {
            $this->__QUERY__[__FUNCTION__] = true;
            return $this;
        }

        $values = $this->__QUERY__[__FUNCTION__] ?? [];
        $values  = is_array($values) ? $values : [$values];
        $this->__QUERY__[__FUNCTION__] = array_values(array_filter($this->flatten(array_merge($values, $columns)), function ($column) {
            return is_string($column);
        }));
        return $this;
    }

    public function when($value, ?callable $query = null, ?callable $default = null)
    {
        $callback = function ($builder) {
            return $builder;
        };

        if ($query) {
            $callback = function ($q) use ($query, $value, $default) {
                $value = $value instanceof \Closure ? $value($this) : $value;
                return $value ? $query($q, $value) : ($default ? $default($q, $value) : $q);
            };
        }

        return $callback($this) ?? $this;
    }

    /**
     * Set the list of columns to exclude from the rest query result.
     *
     * @param string[] $columns
     *
     * @return static
     */
    public function excludes(...$columns)
    {
        // code...
        $this->__EXCLUDES__ = array_unique(array_merge($this->__EXCLUDES__ ?? [], $this->flatten($columns)));

        return $this;
    }

    /**
     * Get the raw __QUERY__ property value.
     *
     * @return array|mixed
     */
    public function getRawQuery(?string $method = null)
    {
        return $method ? ($this->__QUERY__[$method] ?? null) : ($this->__QUERY__ ?? []);
    }

    /**
     * get compiled query.
     * 
     * @template TReturnType
     *
     * @param TReturnType|null $default
     * 
     * @return array|TReturnType|null
     */
    public function getQuery(?string $method = null, $default = null)
    {
        return $method ? (isset($this->__QUERY__[$method]) ? PreparesFiltersArray::doPrepare($this->__QUERY__[$method], $method) : $default ?? null) : PreparesFiltersArray::new($this->__QUERY__)->call() ?? [];
    }

    /**
     * Get __COLUMNS__ property value.
     *
     * @return array
     */
    public function getColumns()
    {
        // code...
        return $this->__COLUMNS__ ?? [];
    }

    /**
     * Get __EXCLUDES__ property value.
     *
     * @return array
     */
    public function getExcludes()
    {
        // code...
        return $this->__EXCLUDES__ ?? [];
    }

    /**
     * Append a query value to the __QUERY__ property value if the method name is missing as key in __QUERY__.
     *
     * @param mixed $value
     *
     * @return void
     */
    private function appendQuery(string $method, $value)
    {
        if (!isset($this->__QUERY__[$method])) {
            $this->__QUERY__[$method] = [];
        }
        $this->__QUERY__[$method][] = $value;
    }

    /**
     * Set the existance query filter.
     *
     * @param mixed  $query
     * @param string $method
     *
     * @return static
     */
    private function setExistQuery(string $as, $query, $method = 'exists')
    {
        $query = $query instanceof \Closure ? new SubQuery('and', $query(static::new())->getQuery()) : $query;
        // Case the query is a subquery object we returns the json representation of the query
        $query = $query instanceof SubQuery ? ['column' => $as, 'match' => $query->json()] : (null === $query ? $as : [$as, $query]);

        $this->appendQuery($method, $query);
    }

    /**
     * Construct and set the actual where query object.
     *
     * @param mixed $column
     * @param mixed $operatorOrValue
     * @param mixed $value
     *
     * @return void
     */
    private function setWhereQuery(string $method, $column, $operatorOrValue = null, $value = null)
    {
        $this->__QUERY__ = $this->__QUERY__ ?? [];
        $query = (!isset($operatorOrValue) && !isset($value)) ? ($column instanceof SubQuery ? $column->json() : $column) : (isset($operatorOrValue) && !isset($value) ? [$column, '=', $operatorOrValue] : [$column, $operatorOrValue, $value]);
        // Add the % prefix and suffix if query operator is a `like` or `match` query
        if (isset($query[1]) && (('like' === $query[1]) || ('match' === $query[1])) && isset($query[2])) {
            $query[2] = str_contains((string) $query[2], '%') ? $query[2] : '%' . (string) $query[2] . '%';
        }
        $this->appendQuery($method, $query);
    }

    /**
     * Flatten a multi-dimensional array into a single dimensional array.
     *
     * @return array
     */
    private function flatten(array $values)
    {
        $generator = static function ($values, &$output) use (&$generator) {
            foreach ($values as $value) {
                if (is_iterable($value)) {
                    $generator($value, $output);
                    continue;
                }
                $output[] = $value;
            }
        };
        $out = [];
        $generator($values, $out);

        return $out;
    }
}
