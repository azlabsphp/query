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
use Drewlabs\Query\Contracts\FiltersInterface;
use Drewlabs\Query\Contracts\PreparesQuery;

/**
 * @internal
 */
final class PreparesBaseQuery implements PreparesQuery
{
    public function __invoke($params)
    {
        if (empty($params)) {
            return [];
        }

        if (\is_array($params)) {

            $isKv = Arr::isassoc($params);
            
            if (!$isKv) {
                return !(array_filter($params, 'is_array') === $params) ? $params : array_map(static function ($q) {
                    return (new static())($q);
                }, $params);
            }

            if (isset($params['match'])) {
                return MatchSubqueryFactory::new()->create($params['match']);
            }

            if (isset($params['method']) && isset($params['params'])) {
                return function (FiltersInterface $instance, $builder) use ($params) {

                    // TODO: Check if params is an array list and build the statement based on that
                    return QueryStatementsReducer::new([new QueryStatement($params['method'], $params['params'])])->call($instance, $builder);
                };
            }

            return function (FiltersInterface $instance, $builder) use ($params) {
                return QueryStatementsReducer::new(array_reduce(array_keys($params), function ($carry, $method) use ($params) {
                    array_push($carry, new QueryStatement($method, $params[$method]));
                    return $carry;
                }, []))->call($instance, $builder);
            };
        }

        return $params;
    }
}
