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

interface Queryable
{
    /**
     * returns the unique identifying key of the queryable instance.
     *
     * @return mixed
     */
    public function getPrimaryKey();

    /**
     * returns the collection attached to the queryable instance.
     *
     * @return string
     */
    public function getTable();

    /**
     * returns the list of declared columns of the queryable instance.
     *
     * @return array<string>
     */
    public function getDeclaredColumns();
}
