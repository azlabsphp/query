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

class PreparesExistQuery implements PreparesQuery
{
    public function __invoke($params)
    {
        if (!\is_array($params)) {
            return [$params];
        }

        $isKvPair = array_keys($params) !== range(0, \count($params) - 1);
        if ($isKvPair && isset($params['match'], $params['column'])) {
            return (new PreparesSubQuery())($params);
        }
    
        return $params;
    }
}
