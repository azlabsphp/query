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

interface InputBagInterface
{
    /**
     * Checks if attribute exists in the input bag.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name);

    /**
     * return attribute `$nme` value from input bag.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get($name);

    /**
     * returns all values from the input bag.
     *
     * @return array<mixed>|array<string,mixed>
     */
    public function all();
}
