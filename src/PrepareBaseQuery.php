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

use Drewlabs\Query\Contracts\FiltersInterface;
use Drewlabs\Query\Contracts\PreparesQuery;

final class PrepareBaseQuery implements PreparesQuery
{
    public function __invoke($params)
    {
        // Case the query parameters is empty, return the parameters as it's
        if (empty($params)) {
            return $params;
        }
    
        $isKvPair = array_keys($params) !== range(0, \count($params) - 1);
        if (\is_array($params) && !$isKvPair && (array_filter($params, 'is_array') === $params)) {
            return array_map(static function ($q) {
                return (new static())($q);
            }, $params);
        }

        if ($isKvPair && isset($params['match'])) {
            return MatchSubqueryFactory::new()->create($params['match']);
        }
        if ($isKvPair) {
            return function(FiltersInterface $instance, $builder) use ($params) {
                return QueryStatementsReducer::new(array_reduce(array_keys($params), function($carry, $key) use ($params) {
                    $carry[] = new QueryStatement($key, $params[$key]);
                    return $carry;
                }, []))->call($instance, $builder);
            };
        }

        return $params;
    }
}
