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
use Drewlabs\Query\Contracts\PreparesQuery;

class PreparesSubQuery implements PreparesQuery
{
    public function __invoke($params)
    {
        if (!($isKvPair = array_keys($params) !== range(0, \count($params) - 1)) && (((array_filter($params, 'is_array') === $params)) && !$isKvPair)) {
            return array_map(function ($params) {
                return [$params['column'], $this->subQueryFactory($params['match'])];
            }, $params);
        }

        return [$params['column'], $this->subQueryFactory($params['match'])];
    }

    /**
     * Creates a factory function that get call on query filters.
     *
     * @param mixed $query
     *
     * @return Closure(mixed $q): mixed
     */
    private function subQueryFactory($query)
    {
        static::validateFilters($query);
        [$method, $params] = [$query['method'], ['params']];

        return static function ($q) {
            // $method = self::QUERY_METHODS[$method] ?? $method;
            // if ((((array_filter($params, 'is_array') === $params)) && !(array_keys($params) !== range(0, \count($params) - 1)))) {
            //     \call_user_func([$q, $method], $params);
            // } else {
            //     \call_user_func([$q, $method], ...$params);
            // }
            // TODO: Build a filters instance and invoke it with params
        };
    }

    /**
     * Validate sub query parameters.
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    private function validateFilters(array $params)
    {
        if (!isset($params['method']) || !isset($params['params'])) {
            throw new \InvalidArgumentException('The query object requires "method" and "params" keys');
        }
    }
}
