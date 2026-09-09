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

use Drewlabs\Core\Helpers\Arr;
use Drewlabs\Core\Helpers\Functional;
use Drewlabs\Core\Helpers\Str;
use Drewlabs\Query\Contracts\FilterBagInterface;
use Drewlabs\Query\Contracts\FiltersInterface;
use Drewlabs\Query\Contracts\Queryable as AbstractQueryable;
use Drewlabs\Query\Sanitizers\Expression;
use Drewlabs\Query\Utils\FiltersBag;
use Drewlabs\Query\Utils\Queryable as UtilsQueryable;

/**
 * @internal
 */
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
     * @param AbstractQueryable|\Closure(): AbstractQueryable|null $queryable
     *
     * @return array<string, mixed>
     */
    public function call($queryable = null, array $defaults = [])
    {
        $queryable = !\is_string($queryable) && \is_callable($queryable) ? \call_user_func($queryable) : (null === $queryable ? new UtilsQueryable() : $queryable);

        $bag = \is_array($this->bag) || null === $this->bag ? FiltersBag::new($this->bag ?? []) : $this->bag;

        return Functional::compose(
            static function (AbstractQueryable $instance) use ($bag, $defaults) {
                return static::fromQueryParams($instance, $bag, $defaults);
            },
            static function ($filters) use ($bag) {
                return static::fromBody($bag, $filters);
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
    public static function fromQueryParams(AbstractQueryable $queryable, $bag, $defaults = [])
    {
        $bag = \is_array($bag) || null === $bag ? FiltersBag::new($bag ?? []) : $bag;

        $filters = iterator_to_array(static::mapToFilter(static function ($filter) {
            return \is_array($filter) && array_filter($filter, 'is_array') === $filter ? $filter : [$filter];
        }, $defaults ?? []));

        if ($bag->has($queryable->getPrimaryKey()) && null !== $bag->get($queryable->getPrimaryKey())) {
            $filters['and'][] = [$queryable->getPrimaryKey(), $bag->get($queryable->getPrimaryKey())];
        }

        foreach ($bag->all() as $key => $value) {
            if (\is_string($value) && Str::contains($value, '|')) {
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
    public static function fromBody($bag, $output = [])
    {
        $bag = \is_array($bag) || null === $bag ? FiltersBag::new($bag ?? []) : $bag;

        $output = $output ?? [];

        if ($bag->has('_query')) {
            $query = $bag->get('_query');
            $query = \is_string($query) ? json_decode($query, true) : (array) $query;

            if (!\is_array($query) || !(is_array($query) && array_keys($query) !== range(0, \count($query) - 1))) {
                return $output;
            }

            foreach (
                [
                    'exists',
                    'whereHas',
                    'and',
                    'where',
                    'or',
                    'orWhere',
                    'in',
                    'whereIn',
                    'notIn',
                    'notin',
                    'whereNotIn',
                    'wherenotin'
                ] as $name
            ) {
                if (isset($query[$name])) {
                    $value = $query[$name];
                    unset($query[$name]);
                    $query[Filters::get($name)] = Arr::isList($value) ? $value : [$value];
                }
            }

            $array = [];
            PreparesFiltersArray::new($query)->prepareInto($array);

            $factory = function (FiltersInterface $instance, $builder) use ($output) {
                $output = $output ?? [];
                $expressions = [];
                foreach ($output as $key => $value) {
                    $expressions[] = new Expression($key, $value);
                }

                return array_reduce($expressions, function ($carry, $expression) use ($builder) {
                    return $expression->apply($carry, $builder);
                }, $instance);
            };

            $output = array_merge_recursive(
                $array,
                isset($array['or']) && !isset($array['and']) && isset($output['or']) ? ['or' => !empty($output) ? [$factory] : []] : ['and' => !empty($output) ? [$factory] : []]
            );
        }

        return $output;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    private static function createSubQuery(array $array, $key, $value, AbstractQueryable $queryable)
    {
        if (\in_array($key, array_diff($queryable->getDeclaredColumns(), $queryable->getDeclaredRelations()), true)) {
            [$operator, $value, $method] = static::parseVal($value);
            $array[$method ?? 'or'][] = [$key, $operator, $value];
        } elseif (Str::contains($key, ['__'])) {
            [$name, $column] = [Str::beforeLast('__', $key), Str::afterLast('__', $key)];
            $name = Str::replace([':', '%', '__'], '.', $name ?? '');
            if (null !== $column && (false !== array_search(Str::contains($name, '.') ? Str::before('.', $name) : $name, $queryable->getDeclaredRelations(), true))) {
                $existsQuery = static::getExistsQueryName($value);
                [$operator, $value, $method] = static::parseVal($value);
                $array[$existsQuery][] = ['column' => $name, 'match' => ['method' => \is_array($value) ? 'in' : 'and', 'params' => [$column, $operator, $value]]];
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
    private static function parseVal($value)
    {
        [$method, $operators, $operator] = ['or', static::QUERY_OPERATORS, null];

        foreach ($operators as $current) {
            if (Str::startsWith((string) $value, "and:$current:")) {
                [$method, $value, $operator] = ['and', Str::after("and:$current:", $value), $current];
                break;
            }

            if (Str::startsWith((string) $value, "&&:$current:")) {
                [$method, $value, $operator] = ['and', Str::after("&&:$current:", $value), $current];
                break;
            }

            if (Str::startsWith((string) $value, "$current:")) {
                [$value, $operator] = [Str::after("$current:", $value), $current];
                break;
            }
        }

        if (Str::startsWith((string) $value, 'and:')) {
            [$method, $value] = ['and', Str::after('and:', $value)];
        } elseif (Str::startsWith((string) $value, '&&:')) {
            [$method, $value] = ['and', Str::after('&&:', $value)];
        }

        $operator = $operator ?? '=';

        $operator = strtolower($operator) === '=like' ? 'like' : ($operator == '==' ? '=' : $operator);
        $value = $operator === 'like' ? '%' . trim(strval($value), '%') . '%' : $value;

        $method = !is_numeric($value) && false !== strtotime((string) $value) ? ('or' === $method ? 'orDate' : 'date') : $method;

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
    private static function getExistsQueryName($value)
    {
        return Str::startsWith((string) $value, 'and:') || Str::startsWith((string) $value, '&&:') ? 'exists' : 'orExists';
    }
}
