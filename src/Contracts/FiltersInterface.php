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

/**
 * @template TBuilder of object
 */
interface FiltersInterface extends AbstractFilters
{
    /**
     * Invoke the current filters object on the builder instance
     * to build the query platform dependant query.
     *
     * @return TBuilder
     */
    public function __invoke($args);

    /**
     * Invoke the current filters object on the builder instance
     * to build the query platform dependant query.
     *
     * @return TBuilder
     */
    public function apply($builder);

    /**
     * Set the `filters` property to the value of the parameter.
     *
     * @return self
     */
    public function setQueryFilters(array $list);
}
