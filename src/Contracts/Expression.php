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

use Drewlabs\Query\Contracts\FiltersInterface;

interface Expression
{
    /**
     * returns the expression name
     * 
     * @return string 
     */
    public function getName(): string;

    /**
     * returns expression's parameter list
     * 
     * @return mixed
     */
    public function getParams();

    /**
     * method implementation might change in future as builder instance can possibly be null
     * 
     * @param FiltersInterface $instance
     * 
     * @param mixed|null $builder
     * 
     * @return FiltersInterface 
     */
    public function apply(FiltersInterface $instance, $builder): FiltersInterface;
}
