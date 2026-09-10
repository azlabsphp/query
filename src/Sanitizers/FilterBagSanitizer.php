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

namespace Drewlabs\Query\Sanitizers;

use Drewlabs\Core\Helpers\Arr;
use Drewlabs\Core\Helpers\Str;
use Drewlabs\Query\AST\Builder;
use Drewlabs\Query\Contracts\FilterBagInterface;
use Drewlabs\Query\Contracts\FiltersInterface;
use Drewlabs\Query\Contracts\Queryable as AbstractQueryable;
use Drewlabs\Query\Filters;
use Drewlabs\Query\Utils\FiltersBag;
use Drewlabs\Query\Utils\Queryable as UtilsQueryable;

final class FilterBagSanitizer
{
    /**
     * list of query operator supported by the Query Filters handler.
     *
     * @var string[]
     */
    const QUERY_OPERATORS = ['>=', '<=', '<', '>', '<>', '=like', '=='];

    /** @var AbstractQueryable|\Closure(): AbstractQueryable|null $model */
    private $model;

    /**
     * @param AbstractQueryable|\Closure(): AbstractQueryable|null $model
     * 
     * @return void 
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * creates Query filters from parameter bag request.
     *
     * @param FilterBagInterface|array|null $param
     * @param array|null $filters
     *
     * @return array<string, mixed>
     */
    public function apply($param, $filters = [])
    {
        $model = !\is_string($this->model) && \is_callable($this->model) ? \call_user_func($this->model) : (null === $this->model ? new UtilsQueryable() : $this->model);
        $param = \is_array($param) || null === $param ? FiltersBag::new($param ?? []) : $param;

        $filters = $filters ?? [];
        $filters = $this->parseParams($model, $param, $filters);

        return $this->parseQueryParam($param, $filters);
    }




    /**
     * @internal
     *
     * build filters from parameter bags.
     *
     * @param FilterBagInterface|array $instance
     * @param array                    $filters
     *
     * @return array<string, mixed>
     */
    public function parseParams(AbstractQueryable $model, $instance, $filters = [])
    {
        $instance = \is_array($instance) || null === $instance ? FiltersBag::new($instance ?? []) : $instance;

        $filters = iterator_to_array($this->mapToFilter(static function ($filter) {
            return \is_array($filter) && array_filter($filter, 'is_array') === $filter ? $filter : [$filter];
        }, $filters ?? []));

        if ($instance->has($model->getPrimaryKey()) && null !== $instance->get($model->getPrimaryKey())) {
            $filters['and'][] = [$model->getPrimaryKey(), $instance->get($model->getPrimaryKey())];
        }

        $junction = $instance->get('_junction') ?? $instance->get('_logic') ?? 'or';
        $values = Arr::except($instance->all(), ['per_page', 'page', '_sort', '_limit', '_junction', '_logic', '_query']);
        $columns = array_diff($model->getDeclaredColumns(), $model->getDeclaredRelations());
        $relations = $model->getDeclaredRelations();

        foreach ($values as $key => $value) {
            if (\is_string($value) && Str::contains($value, '|')) {
                $items = \is_string($value) && Str::contains($value, '|') ? Str::split($value, '|') : $value;
                foreach ($items as $item) {
                    $filters = $this->createsubQuery($filters, $key, $item, $columns, $relations, $junction);
                }
                continue;
            }

            if (!empty($value)) {
                $filters = $this->createsubQuery($filters, $key, $value, $columns, $relations, $junction);
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
     * @param FilterBagInterface|array $instance 
     * @param array $filters
     * 
     * @return array 
     */
    public function parseQueryParam($instance, $filters = [])
    {

        $instance = \is_array($instance) || null === $instance ? FiltersBag::new($instance ?? []) : $instance;

        $filters = $filters ?? [];

        if ($instance->has('_query')) {

            $query = $instance->get('_query');
            $query = \is_string($query) ? json_decode($query, true) : (array) $query;

            if (is_string($query)) {
                $builder = new Builder;
                $query = $builder->build($query)->toDict();
            }

            if (!\is_array($query) || (is_array($query) && !Arr::isassoc($query))) {
                return $filters;
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

            $sanitizer = new ArraySanitizer;
            $array = $sanitizer->apply($query);

            $factory = function (FiltersInterface $instance, $builder) use ($filters) {
                $filters = $filters ?? [];
                $expressions = [];
                foreach ($filters as $key => $value) {
                    $expressions[] = new Expression($key, $value);
                }

                return array_reduce($expressions, function ($carry, $expression) use ($builder) {
                    return $expression->apply($carry, $builder);
                }, $instance);
            };

            $filters = array_merge_recursive(
                $array,
                isset($array['or']) && !isset($array['and']) && isset($filters['or']) ? ['or' => !empty($filters) ? [$factory] : []] : ['and' => !empty($filters) ? [$factory] : []]
            );
        }

        return $filters;
    }



    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    private function createsubQuery(array $array, $key, $value, array $columns, array $relations, string $junction = 'or')
    {
        if (Str::contains($key, ['__'])) {
            [$name, $column] = [Str::beforeLast('__', $key), Str::afterLast('__', $key)];
            $name = Str::replace([':', '%', '__'], '.', $name ?? '');
            if (null !== $column && (false !== array_search(Str::contains($name, '.') ? Str::before('.', $name) : $name, $relations, true))) {
                $existsQuery = $this->getExistsExpressionName($value);
                [$operator, $value, $method] = $this->parseVal($value, $junction);
                $array[$existsQuery][] = ['column' => $name, 'match' => ['method' => \is_array($value) ? 'in' : 'and', 'params' => [$column, $operator, $value]]];
            }

            return $array;
        }

        if (\in_array($key, $columns, true)) {
            [$operator, $value, $method] = $this->parseVal($value, $junction);
            $array[$method ?? 'or'][] = [$key, $operator, $value];
        }

        return $array;
    }

    /**
     * parse the value in order to return the query method to apply and the operator that is needed to be used.
     *
     * @param string $value
     *
     * @return array
     */
    private function parseVal($value, string $junction = 'or')
    {
        $method = $junction;
        $operators = static::QUERY_OPERATORS;
        $operator = null;

        if (Str::startsWith((string) $value, 'and:')) {
            [$method, $value] = ['and', Str::after('and:', $value)];
        }

        if (Str::startsWith((string) $value, 'date:')) {
            [$method, $value] = ['or' === $method ? 'orDate' : 'date', Str::after('date:', $value)];
        }

        foreach ($operators as $current) {
            if (Str::startsWith((string) $value, "$current:")) {
                [$value, $operator] = [Str::after("$current:", $value), $current];
                break;
            }
        }

        $operator = $operator ?? '=';
        $operator = strtolower($operator) === '=like' ? 'like' : ($operator == '==' ? '=' : $operator);
        $value = $operator === 'like' ? '%' . trim(strval($value), '%') . '%' : $value;

        if (is_string($value)) {
            foreach (['bool', 'int', 'float'] as $op) {
                if (Str::startsWith(trim($value), "$op:")) {
                    $rawVal = mb_substr($value, strlen("$op:"));
                    $value = match ($op) {
                        'bool' => in_array($rawVal, ['false', 'FALSE', '0']) ? false : true,
                        'int' => intval($rawVal),
                        'float' => floatval($rawVal),
                        default => $rawVal
                    };

                    break;
                }
            }
        }

        return [$operator, $value, $method];
    }

    /**
     * prepares default query filters.
     *
     * @return \Traversable<string, mixed, mixed, void>
     */
    private function mapToFilter(callable $callback, array $default = [])
    {
        foreach ($default ?? [] as $key => $value) {
            yield Filters::get($key) => $callback($value);
        }
    }

    /**
     * get sub query method based on the provided value.
     *
     * @param mixed $value
     *
     * @return string
     */
    private function getExistsExpressionName($value)
    {
        return Str::startsWith((string) $value, 'and:') || Str::startsWith((string) $value, '&&:') ? 'exists' : 'orExists';
    }
}
