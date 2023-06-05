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
use Drewlabs\Query\Contracts\FiltersInterface;
use Drewlabs\Query\Contracts\PreparesQuery;

class PreparesSubQuery implements PreparesQuery
{
    public function __invoke($params)
    {
        if (!($isKvPair = array_keys($params) !== range(0, \count($params) - 1)) && ((array_filter($params, 'is_array') === $params) && !$isKvPair)) {
            return array_map(static function ($params) {
                return [$params['column'], static::subQueryFactory($params['match'])];
            }, $params);
        }

        return [$params['column'], static::subQueryFactory($params['match'])];
    }

    /**
     * Creates a factory function that get call on query filters.
     *
     * @param mixed $query
     *
     * @return Closure(mixed $q): mixed
     */
    public static function subQueryFactory($query)
    {
        return static function (FiltersInterface $instance, $builder) use ($query) {
            // Compiles subquery into dictionnary case the subquery is a string or a list of values
            $query = (new PreparesMatchQuery)->__invoke($query);
            [$method, $params] = [$query['method'], $query['params']];
            // Prepare the query filters into the output variable to ensure method matches supported method
            $result = PreparesFiltersArray::doPrepare($params, $method = Filters::get($method));
            return $instance->invoke($method, $builder, $result);
        };
    }
}
