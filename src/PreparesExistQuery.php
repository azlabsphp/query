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

/**
 * @internal
 */
class PreparesExistQuery implements PreparesQuery
{
    public function __invoke($params)
    {
        // Case the query parameters is empty, return the parameters as it's
        if (empty($params)) {
            return $params;
        }

        if (!\is_array($params)) {
            return [$params];
        }

        // Case `params` is a not key - value pair, we, we check if it's an array list and perform a reduce
        // transformation on the array list to create the prepared subquery
        return (new PreparesSubQuery())($params);
    }
}
