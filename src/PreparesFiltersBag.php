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

use Drewlabs\Core\Helpers\Functional;
use Drewlabs\Core\Helpers\Str;
use Drewlabs\Query\Contracts\FilterBagInterface;
use Drewlabs\Query\Contracts\Queryable;
use Drewlabs\Query\Utils\FiltersBag;
use Drewlabs\Query\Utils\Queryable as UtilsQueryable;

final class PreparesFiltersBag
{
    /**
     * List of query operator supported by the Query Filters handler.
     *
     * @var string[]
     */
    private const QUERY_OPERATORS = ['>=', '<=', '<', '>', '<>', '=like', '=='];

    /**
     * @var FilterBagInterface
     */
    private $bag;

    /**
     * Creates class instances.
     *
     * @param FilterBagInterface $bag
     *
     * @return void
     */
    public function __construct($bag)
    {
        $this->bag = $bag;
    }

    /**
     * Creates new class instance.
     *
     * @param FilterBagInterface $bag
     *
     * @return self
     */
    public static function new($bag)
    {
        return new static($bag);
    }

    /**
     * Creates Query filters from parameter bag request.
     *
     * @param Queryable|\Closure(): Queryable|null $queryable
     *
     * @return array<string, mixed>
     */
    public function call($queryable = null, array $defaults = [])
    {
        $queryable = !\is_string($queryable) && \is_callable($queryable) ? \call_user_func($queryable) : (null === $queryable ? new UtilsQueryable() : $queryable);

        // We first make sure the queryBag variable is resolved to `InputBagInterface` instance
        $bag = \is_array($this->bag) || null === $this->bag ? FiltersBag::new($this->bag ?? []) : $this->bag;

        // Compose list of function to apply to queryable instance and $inputBag
        return Functional::compose(
            static function (Queryable $instance) use ($bag, $defaults) {
                return static::from_Query_Parameters($instance, $bag, $defaults);
            },
            static function ($filters) use ($bag) {
                return static::from_Query_Body($bag, $filters);
            }
        )($queryable);
    }

    /**
     * @internal
     *
     * Build filters from parameter bags.
     *
     * **Note** It's an internal API implementation, do not use directly as the API might change
     *
     * @param FilterBagInterface|array $bag
     * @param array                    $defaults
     *
     * @return array<string, mixed>
     */
    public static function from_Query_Parameters(Queryable $queryable, $bag, $defaults = [])
    {
        // We first make sure the queryBag variable is resolved to `InputBagInterface` instance
        $bag = \is_array($bag) || null === $bag ? FiltersBag::new($bag ?? []) : $bag;

        $filters = iterator_to_array(static::mapToFilter(static function ($filter) {
            // We check first if the filter is an array. If the filter is an array,
            // we then we check if the array is an array of arrays (1). If case (1) resolves
            // to true, we return the filter, else we wrap the filter in an array
            return \is_array($filter) && array_filter($filter, 'is_array') === $filter ? $filter : [$filter];
        }, $defaults ?? []));
        if ($bag->has($queryable->getPrimaryKey()) && null !== $bag->get($queryable->getPrimaryKey())) {
            $filters['and'][] = [$queryable->getPrimaryKey(), $bag->get($queryable->getPrimaryKey())];
        }
        foreach ($bag->all() as $key => $value) {
            if (\is_string($value) && Str::contains($value, '|')) {
                // For composed value, if the value is a string and contains | character we split the value using
                // the | character and foreach item in the splitted list we add a filter
                $items = \is_string($value) && Str::contains($value, '|') ? Str::split($value, '|') : $value;
                foreach ($items as $item) {
                    $filters = static::createSubQuery($filters, $key, $item, $queryable);
                }
                continue;
            }
            if (!empty($value)) {
                $filters = static::createSubQuery($filters, $key, $value, $queryable);
                continue;
            }
        }
        // order this query method in the order of and -> exists -> or
        // Write a better algorithm for soring
        uksort($filters, static function ($prev, $curr) {
            if ('and' === $prev) {
                return -1;
            }
            if (('exists' === $prev) && ('and' === $curr)) {
                return 1;
            }
            if (('exists' === $prev) && ('or' === $curr)) {
                return -1;
            }

            if (('orExists' === $prev) && ('or' === $curr)) {
                return -1;
            }

            if ('or' === $prev) {
                return 1;
            }
        });

        return $filters;
    }

    /**
     * @internal
     *
     * Build query filters using '_query' property of the parameter bag.
     *
     * **Note** It's an internal API implementation, do not use directly as the API might change
     *
     * @param FilterBagInterface|array $bag
     * @param array                    $output
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public static function from_Query_Body($bag, $output = [])
    {
        // We first make sure the queryBag variable is resolved to `InputBagInterface` instance
        $bag = \is_array($bag) || null === $bag ? FiltersBag::new($bag ?? []) : $bag;
        // Set the default fot the output variable
        $output = $output ?? [];
        if ($bag->has('_query')) {
            $query = $bag->get('_query');
            $query = \is_string($query) ? json_decode($query, true) : (array) $query;

            // Decoded query variable must be an associatve array, else we do not proceed in the context execution
            if (!\is_array($query) || !(array_keys($query) !== range(0, \count($query) - 1))) {
                return $output;
            }

            // Prepare the array filters into the output variable
            PreparesFiltersArray::new($query)->prepareInto($output);
        }

        return $output;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    private static function createSubQuery(array $array, $key, $value, Queryable $queryable)
    {
        if (\in_array($key, array_diff($queryable->getDeclaredColumns(), $queryable->getDeclaredRelations()), true)) {
            [$operator, $value, $method] = static::operatorValueTuple($value);
            $array[$method ?? 'or'][] = [$key, $operator, $value];
        } elseif (Str::contains($key, ['__'])) {
            [$name, $column] = [Str::beforeLast('__', $key), Str::afterLast('__', $key)];
            $name = Str::replace([':', '%', '__'], '.', $name ?? '');
            if (null !== $column && (false !== array_search(Str::contains($name, '.') ? Str::before('.', $name) : $name, $queryable->getDeclaredRelations(), true))) {
                $existsQuery = static::getSubQueryMethod($value);
                [$operator, $value, $method] = static::operatorValueTuple($value);
                $array[$existsQuery][] = ['column' => $name, 'match' => ['method' => \is_array($value) ? 'in' : $method ?? 'and', 'params' => [$column, $operator, $value]]];
            }
        }

        return $array;
    }

    /**
     * Parse the value in order to return the query method to apply and the operator
     * that is needed to be used.
     *
     * @param string $value
     *
     * @return array
     */
    private static function operatorValueTuple($value)
    {
        // We use == to represent = db comparison operator
        [$method, $operators, $operator] = ['or', static::QUERY_OPERATORS, null];

        foreach ($operators as $current) {
            // By default we apply the query with or and clause. But in case the developper pass a query string
            // with &&: or and: operator we query using the and clause
            if (Str::startsWith((string) $value, "and:$current:")) {
                [$method, $value, $operator] = ['and', Str::after("and:$current:", $value), $current];
                break;
            } elseif (Str::startsWith((string) $value, "&&:$current:")) {
                [$method, $value, $operator] = ['and', Str::after("&&:$current:", $value), $current];
                break;
            } elseif (Str::startsWith((string) $value, "$current:")) {
                [$value, $operator] = [Str::after("$current:", $value), $current];
                break;
            }
        }
        if (Str::startsWith((string) $value, 'and:')) {
            [$method, $value] = ['and', Str::after('and:', $value)];
        } elseif (Str::startsWith((string) $value, '&&:')) {
            [$method, $value] = ['and', Str::after('&&:', $value)];
        }
        $operator = $operator ?? (is_numeric($value) || \is_bool($value) ? '=' : 'like');
        // If the operator is a like operator, we removes any % from start and end of value
        // And append our own. We also make sure the operator is like instead of =like
        if (('=like' === $operator) || ('like' === $operator)) {
            [$value, $operator] = ['%'.trim($value, '%').'%', 'like'];
        } elseif ('==' === $operator) {
            $operator = '=';
        }
        $method = false !== strtotime((string) $value) ? ('or' === $method ? 'orDate' : 'date') : $method;

        return [$operator, $value, $method];
    }

    /**
     * Prepare default query filters.
     *
     * @return \Traversable<string, mixed, mixed, void>
     */
    private static function mapToFilter(callable $callback, array $default = [])
    {
        foreach ($default ?? [] as $key => $value) {
            yield Filters::get($key) => $callback($value);
        }
    }

    /**
     * Get sub query method based on the provided value.
     *
     * @param mixed $value
     *
     * @return string
     */
    private static function getSubQueryMethod($value)
    {
        return Str::startsWith((string) $value, 'and:') || Str::startsWith((string) $value, '&&:') ? 'exists' : 'orExists';
    }
}
