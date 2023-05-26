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
use InvalidArgumentException;

class ArrayFiltersBuilder
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
     * **Note** It's an internal API implementation, do not use directly as the API might change
     *
     * @param InputBagInterface $inputBag
     * @param array             $defaults
     *
     * @return array<string, mixed>
     */
    public static function from_Parameters(Queryable $instance, $inputBag, $defaults = [])
    {
        $filters = iterator_to_array(self::prepare_Default_Filters(static function ($filter) {
            // We check first if the filter is an array. If the filter is an array,
            // we then we check if the array is an array of arrays (1). If case (1) resolves
            // to true, we return the filter, else we wrap the filter in an array
            return \is_array($filter) && array_filter($filter, 'is_array') === $filter ? $filter : [$filter];
        }, $defaults ?? []));
        if ($inputBag->has($instance->getPrimaryKey()) && null !== $inputBag->get($instance->getPrimaryKey())) {
            $filters['and'][] = [$instance->getPrimaryKey(), $inputBag->get($instance->getPrimaryKey())];
        }
        foreach ($inputBag->all() as $key => $value) {
            if (\is_string($value) && Str::contains($value, '|')) {
                // For composed value, if the value is a string and contains | character we split the value using
                // the | character and foreach item in the splitted list we add a filter
                $items = \is_string($value) && Str::contains($value, '|') ? Str::split($value, '|') : $value;
                foreach ($items as $item) {
                    $filters = static::prepare_Array_Filters($filters, $key, $item, $instance);
                }
                continue;
            }
            if (!empty($value)) {
                $filters = static::prepare_Array_Filters($filters, $key, $value, $instance);
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
            if (!\is_array($query) || !(array_keys($query) !== range(0, \count($query) - 1))) {
                return $output;
            }
            
            self::map_Into_Array($query, $output);
        }

        return $output;
    }


    /**
     * @internal
     * 
     * Map query filters into the `$output` array
     * 
     * **Note** It's an internal API implementation, do not use directly as the API might change
     * 
     * @param array $query 
     * @param array $output 
     * @return void 
     * @throws InvalidArgumentException 
     */
    private static function map_Into_Array(array $query, array &$output)
    {
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

    /**
     * Build queries based on list of query parameters.
     *
     * @param array|string|mixed $params
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public static function prepare($params, string $method)
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
     *
     * @return array
     */
    private static function prepare_Array_Filters(array $array, $key, $value, Queryable $queryable)
    {
        if (\in_array($key, array_diff($queryable->getDeclaredColumns(), $queryable->getDeclaredRelations()), true)) {
            [$operator, $value, $method] = static::operator_Value($value);
            $array[$method ?? 'or'][] = [$key, $operator, $value];
        } elseif (Str::contains($key, ['__'])) {
            [$name, $column] = [Str::beforeLast('__', $key), Str::afterLast('__', $key)];
            $name = Str::replace([':', '%', '__'], '.', $name ?? '');
            if (null !== $column && (false !== array_search(Str::contains($name, '.') ? Str::before('.', $name) : $name, $queryable->getDeclaredRelations(), true))) {
                [$operator, $value, $method] = static::operator_Value($value);
                $array['exists'][] = ['column' => $name, 'match' => ['method' => \is_array($value) ? 'in' : $method ?? 'and', 'params' => [$column, $operator, $value]]];
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
    private static function operator_Value($value)
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
    private static function prepare_Default_Filters(callable $callback, array $default = [])
    {
        foreach ($default ?? [] as $key => $value) {
            yield Filters::get($key) => $callback($value);
        }
    }
}