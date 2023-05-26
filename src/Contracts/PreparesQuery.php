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

interface PreparesQuery
{
    /**
     * @psalm-template T of string|int|array|mixed
     *
     * @param T|array $params
     *
     * @return T|array
     */
    public function __invoke($params);
}
