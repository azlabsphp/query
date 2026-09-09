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

namespace Drewlabs\Query\Sanitizers;

use Drewlabs\Query\Contracts\FiltersInterface;
use Drewlabs\Query\Filters;
use Drewlabs\Query\PreparesFiltersArray;

final class Expression
{
    /** @var string */
    private $method;

    /** @var array */
    private $args;

    /**
     * Create new query statement instance.
     *
     * @param array $args
     */
    public function __construct(string $method, array $args)
    {
        $this->method = Filters::get($method);
        $this->args = $args;
    }

    /**
     * Return the query method
     * 
     * @return string 
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * Returns the list statement arguments
     * 
     * @return array 
     */
    public function args()
    {
        return $this->args ?? [];
    }

    /**
     * call the query state on the query driver
     * 
     * @param object $driver 
     * @return mixed 
     */
    public function call(object $driver)
    {
        return call_user_func_array([$driver, $this->method()], $this->args());
    }

    /**
     * invoke the filters instance on the builder with compiled expression
     * 
     * @param FiltersInterface $instance 
     * @param mixed $builder
     * 
     * @return FiltersInterface 
     */
    public function apply(FiltersInterface $instance, $builder)
    {
        return $instance->invoke($this->method, $builder, PreparesFiltersArray::doPrepare($this->args ?? [], $method = $this->method));
    }

    /**
     * Returns the array representation of the statement.
     *
     * @return (string|array)[]
     */
    public function toArray()
    {
        return ['method' => trim($this->method), 'params' => $this->args()];
    }
}
