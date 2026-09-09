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

class Aggregationmethods
{
    /**
     * method signature for count aggregation on query result.
     */
    public const COUNT = 'count';

    /**
     * method signature for max aggregation on query result.
     */
    public const MAX = 'max';

    /**
     * method signature for min aggregation on query result.
     */
    public const MIN = 'min';

    /**
     * method signature for avg aggregation on query result.
     */
    public const AVERAGE = 'avg';

    /**
     * method signature for sum aggregation on query result.
     */
    public const SUM = 'sum';
}
