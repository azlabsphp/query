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

namespace Drewlabs\Query\Utilss;

use Drewlabs\Query\Contracts\FilterBagInterface;

class FiltersBag implements FilterBagInterface
{
    /**
     * Array of filters.
     *
     * @var array<string,array<string,mixed>|mixed>
     */
    private $values;

    /**
     * Creates class instance.
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Creates new filters bag instance.
     *
     * @return static
     */
    public static function new(array $values = [])
    {
        return new static($values ?? []);
    }

    public function clone()
    {
        return static::new($this->all());
    }

    public function has($name)
    {
        return \array_key_exists($name, $this->values) && null !== $name;
    }

    public function get($name)
    {
        return $this->values[$name] ?? null;
    }

    public function all()
    {
        return array_merge(array_filter($this->values, static function ($value) {
            return null !== $value;
        }));
    }
}
