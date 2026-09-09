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

use Drewlabs\Query\Contracts\Expression as AbstractExpression;
use Drewlabs\Query\Contracts\FiltersInterface;
use Drewlabs\Query\Filters;

final class Expression implements AbstractExpression
{
    /** @var string */
    private $name;

    /** @var array */
    private $args;

    /**
     * Create new query statement instance.
     *
     * @param array $args
     */
    public function __construct(string $name, array $args)
    {
        $this->name = Filters::get($name);
        $this->args = $args;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function getParams(): array
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
        return call_user_func_array([$driver, $this->getName()], $this->getParams());
    }

    public function apply(FiltersInterface $instance, $builder): FiltersInterface
    {
        $sanitizer = new Sanitizer($this->name);
        
        $instance->invoke($this->name, $builder, $sanitizer->apply($this->args ?? []));

        return $instance;
    }
}
