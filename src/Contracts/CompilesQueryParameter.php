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

interface CompilesQueryParameter
{
    /**
     * Compiles query parameters by resolving table, operators, etc...
     *
     * @param array $params
     *
     * @return array
     */
    public function compile($params);
}
