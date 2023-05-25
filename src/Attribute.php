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

/**
 * @internal Required internally for parsing query parameter
 *           The API is subject to change as the name can change as well
 *           Therefore using it externally, may lead to breaking changes when internal decisions are made
 */
class Attribute
{
    /**
     * @var string|object
     */
    private $class;

    /**
     * @var string
     */
    private $column;

    /**
     * Creates a query attribute instance.
     *
     * @param array $attributes
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function __construct($attributes = [])
    {
        $this->class = $attributes['model'] ?? null;
        $this->column = $attributes['column'] ?? null;
        $this->validateAttributes();
    }

    public function __toString()
    {
        $model = \is_string($this->class) ? (new $this->class())->getTable() : $this->class->getTable();

        $attributes = array_merge([$model], $this->column ? [$this->column] : []);

        return trim(implode('.', ...$attributes));
    }

    public function toString()
    {
        return $this->__toString();
    }

    private function validateAttributes()
    {
        if (
            (null === $this->class) ||
            (\is_string($this->class) &&
                !class_exists($this->class)) ||
            (\is_object($this->class) &&
                !method_exists($this->class, 'getTable'))
        ) {
            throw new \InvalidArgumentException('Make sure to provide a valid Eloquent model or a
            model with getTable method that returns a string to the ["model" => ModelClass]');
        }
    }
}
