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
            return (new PreparesSubQuery)->subQueryFactory($params['match']);
        }
        if ($isKvPair) {
            return (new PreparesSubQuery)->subQueryFactory($params);
        }

        return $params;
    }
}
