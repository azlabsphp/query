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
use Drewlabs\Query\Contracts\InputBagInterface;
use Drewlabs\Query\Contracts\Queryable;

class QueryFiltersBuilder
{
    /**
     * List of query operator supported by the Query Filters handler.
     *
     * @var string[]
     */
    private const QUERY_OPERATORS = ['>=', '<=', '<', '>', '<>', '=like', '=='];

    /**
     * @var Queryable
     */
    private $queryable;

    /**
     * Creates a new instance of QueryFiltersBuilder.
     *
     * @return void
     */
    public function __construct(Queryable $queryable)
    {
        $this->queryable = $queryable;
    }

    /**
     * Creates new class instance.
     *
     * @param Queryable|class-string<Queryable> $queryable
     * @param mixed                             ...$args
     *
     * @return self
     */
    public static function new($queryable, ...$args)
    {
        return new self(\is_string($queryable) ? new $queryable(...$args) : $queryable);
    }

    /**
     * Creates Query filters from parameter bag
     * request.
     *
     * @param InputBagInterface $inputBag
     *
     * @return array<string, mixed>
     */
    public function build($inputBag, array $defaults = [])
    {
        return Functional::compose(
            static function (Queryable $instance) use ($inputBag, $defaults) {
                return static::from_Parameters($instance, $inputBag, $defaults);
            },
            static function ($filters) use ($inputBag) {
                return static::from__Query($inputBag, $filters);
            }
        )($this->queryable);
    }

    /**
     * Build filters from parameter bags.
     *
     * @param InputBagInterface $inputBag
     * @param array             $defaults
     *
     * @return array<string, mixed>
     */
    public static function from_Parameters(Queryable $instance, $inputBag, $defaults = [])
    {
        $filters = array_map(static function ($filter) {
            // We check first if the filter is an array. If the filter is an array,
            // we then we check if the array is an array of arrays (1). If case (1) resolves
            // to true, we return the filter, else we wrap the filter in an array
            return \is_array($filter) && array_filter($filter, 'is_array') === $filter ? $filter : [$filter];
        }, $defaults ?? []);
        if ($inputBag->has($instance->getPrimaryKey()) && null !== $inputBag->get($instance->getPrimaryKey())) {
            $filters['where'][] = [$instance->getPrimaryKey(), $inputBag->get($instance->getPrimaryKey())];
        }
        foreach ($inputBag->all() as $key => $value) {
            $array = $instance->getDeclaredColumns();
            if (\is_string($value) && Str::contains($value, '|')) {
                // For composed value, if the value is a string and contains | character we split the value using
                // the | character and foreach item in the splitted list we add a filter
                $items = \is_string($value) && Str::contains($value, '|') ? Str::split($value, '|') : $value;
                foreach ($items as $item) {
                    $filters = static::prepare_Array_Filters($filters, $key, $item, $array, $instance);
                }
                continue;
            }
            if (!empty($value)) {
                $filters = static::prepare_Array_Filters($filters, $key, $value, $array, $instance);
                continue;
            }
        }
        // order this query method in the order of where -> whereHas -> orWhere
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
            if ('or' === $prev) {
                return 1;
            }
        });

        return $filters;
    }

    /**
     * Build query filters using '_query' property of the parameter bag.
     *
     * @param InputBagInterface $queryBag
     * @param array             $output
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public static function from__Query($queryBag, $output = [])
    {
        $output = $output ?? [];
        if ($queryBag->has('_query')) {
            $query = $queryBag->get('_query');
            $query = \is_string($query) ? json_decode($query, true) : (array) $query;

            // Decoded query variable must be an associatve array, else we do not proceed in the context execution
            if (\is_array($query) || !(array_keys($query) !== range(0, \count($query) - 1))) {
                return $output;
            }
            foreach ($query as $key => $value) {
                // Initialize the result array
                $results = [];

                // We search for the query key matches in the supported query methods
                if (Filters::exists($key)) {
                    // get the query filters for the current key and set the key value to the resolved value
                    $results = static::prepare($value, $key = Filters::get($key));
                }

                // In case the buildParameters() returns an empty result we simply ignore the provided
                // query method
                if (empty($results)) {
                    continue;
                }

                // We try to merge the current query parameters into existing parameters
                // if they exist in the filters
                if (isset($output[$key])) {
                    if (array_filter($results, 'is_array') === $results) {
                        foreach ($results as $current) {
                            $output[$key][] = $current;
                        }
                    } else {
                        $output[$key][] = $results;
                    }
                    continue;
                }

                if (!\is_array($results)) {
                    $output[$key] = $results;
                    continue;
                }

                // Default case
                $output[$key] = array_merge($output[$key] ?? [], $results);
            }
        }

        return $output;
    }

    /**
     * Build queries based on list of query parameters.
     *
     * @param array|string|mixed $params
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    private static function prepare($params, string $method)
    {
        switch ($method) {
                // Default group
            case 'and':
            case 'date':
            case 'orDate':
            case 'or':
                return (new PrepareBaseQuery())($params);
                // Exists group
            case 'exists':
            case 'notExists':
                return (new PreparesExistQuery())($params);
                // In group
            case 'in':
            case 'notIn':
                return (new PreparesInQuery())($params);
                // Sort group
            case 'sort':
                return (new PreparesOrderByQuery())($params);

                // Null group
            case 'isNull':
            case 'orIsNull':
            case 'notNull':
            case 'orNotNull':
                return (new PreparesNullQuery())($params);
                // case 'between':
                // case 'group':
                // case 'join':
                // case 'limit':

            default:
                return $params;
        }
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param object $model
     *
     * @return array
     */
    private static function prepare_Array_Filters(array $array, $key, $value, array $list, $model)
    {
        if (\in_array($key, $list, true)) {
            [$operator, $value, $method] = static::operatorValue($value);
            $array[$method ?? 'or'][] = [$key, $operator, $value];
        } elseif (Str::contains($key, ['__'])) {
            [$name, $column] = explode('__', $key);
            $name = Str::replace([':', '%'], '.', $name ?? '');
            $name = Str::contains($name, '.') ? Str::before('.', $name) : $name;
            if (method_exists($model, $name) && null !== $column) {
                [$operator, $value, $method] = static::operatorValue($value);
                $array['exists'][] = [
                    'column' => $name,
                    'match' => [
                        'method' => \is_array($value) ? 'in' : $method ?? 'and',
                        'params' => [$column, $operator, $value],
                    ],
                ];
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
    private static function operatorValue($value)
    {
        // We use == to represent = db comparison operator
        [$method, $operators, $operator] = ['or', static::QUERY_OPERATORS, null];

        foreach ($operators as $current) {
            // By default we apply the query with or where clause. But in case the developper pass a query string
            // with &&: or and: operator we query using the where clause
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
}
