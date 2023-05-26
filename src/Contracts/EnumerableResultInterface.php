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

namespace Drewlabs\Query\Contracts;

use Drewlabs\Contracts\Data\EnumerableQueryResult as BaseEnumerableQueryResult;

/**
 * @psalm-template TResult
 */
interface EnumerableResultInterface extends \ArrayAccess, BaseEnumerableQueryResult
{
    /**
     * Returns collection items.
     *
     * @psalm-template TResult
     *
     * @return TResult
     */
    public function getCollection();

    /**
     * alias for `getCollection()`.
     *
     * @psalm-template TResult
     *
     * @return TResult
     */
    public function items();

    /**
     * Set result items.
     *
     * @param mixed $items
     *
     * @return static
     */
    public function setCollection($items);
}
