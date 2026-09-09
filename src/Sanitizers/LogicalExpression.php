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
use Drewlabs\Query\Contracts\FiltersInterface;
use Drewlabs\Query\Contracts\PreparesQuery;

/**
 * @internal
 */
final class LogicalExpression implements PreparesQuery
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
                return Subquery::new()->create($params['match']);
            }

            if (isset($params['method']) && isset($params['params'])) {
                return function (FiltersInterface $instance, $builder) use ($params) {

                    // TODO: Check if params is an array list and build the statement based on that
                    $expression = new Expression($params['method'], $params['params']);
                    return $expression->apply($instance, $builder);
                };
            }

            return function (FiltersInterface $instance, $builder) use ($params) {
                $expressions = array_map(function ($method) use ($params) {
                    return new Expression($method, $params[$method]);
                }, array_keys($params));
                
                return array_reduce($expressions, function ($carry, $expression) use ($builder) {
                    return $expression->apply($carry, $builder);
                }, $instance);
            };
        }

        return $params;
    }
}
