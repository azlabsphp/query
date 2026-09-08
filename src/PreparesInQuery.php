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
use Drewlabs\Query\Contracts\PreparesQuery;

/**
 * @internal
 */
final class PreparesInQuery implements PreparesQuery
{
    public function __invoke($params)
    {
        $isKv = Arr::isassoc($params);
        
        if (!$isKv && (array_filter($params, 'is_array') === $params)) {
            return array_map(static function ($q) {
                return (new static())($q);
            }, $params);
        }

        if (!$isKv) {
            $count = \count($params);
            if (2 !== $count) {
                throw new \InvalidArgumentException('`in` | `notin` query require 2 items first one being the column name and second being the matching array, when not using associative array like ["column" => "col", "match" => $items]');
            }
            return [$params[0], $params[1]];
        }

        if (!isset($params['column']) && !isset($params['match'])) {
            throw new \InvalidArgumentException('`in` | `notin` query requires column key and match key');
        }

        return [$params['column'], $params['match']];
    }
}
