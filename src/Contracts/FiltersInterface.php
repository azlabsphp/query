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

use Drewlabs\Contracts\Data\Filters\FiltersInterface as AbstractFilters;

interface FiltersInterface extends AbstractFilters
{
    /**
     * Invoke the current filters object on the platform builder instance.
     *
     * @template TResult of object
     * @template TArgs of array
     *
     * @param TResult $builder
     * @param TArgs   $args
     *
     * @return TResult
     */
    public function __call(string $method, $builder, $args);

    /**
     * Invoke the current filters object on the platform builder instance.
     *
     * @template TResult of object
     * @template TArgs of array
     *
     * @param TResult $builder
     * @param TArgs   $args
     *
     * @return TResult
     */
    public function invoke(string $method, $builder, $args);

    /**
     * Call query filters on the builder instance.
     *
     * @template T of object
     *
     * @param T $builder
     *
     * @return T
     */
    public function apply($builder);

    /**
     * Set the `filters` property to the value of the parameter.
     *
     * @return self
     */
    public function setQueryFilters(array $list);
}
