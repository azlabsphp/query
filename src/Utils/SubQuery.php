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

namespace Drewlabs\Query\Utils;

use Drewlabs\Query\Builder;
use Drewlabs\Query\Contracts\FiltersBuilderInterface;

class SubQuery
{
    /**  @var string */
    private $method;

    /** @var array|mixed */
    private $builder;


    /** @var \Closure(): array */
    private $fn;

    /**
     * creates class instance.
     * 
     * @param \Closure(FiltersBuilderInterface $b):FiltersBuilderInterface
     */
    public function __construct(string $method, \Closure $fn)
    {
        $this->method = $method;
        $this->fn = $fn;
    }

    /**
     * method applied in the subquery.
     *
     * @return self
     */
    public function method(string $name)
    {
        // code...
        $this->method = $name;

        return $this;
    }

    /**
     * parameters to apply in the subquery.
     *
     * @param array|int|float|string $params
     *
     * @return self
     */
    public function setParameters($params)
    {
        $this->builder = $params;

        return $this;
    }

    /**
     * returns a JSON serializable object implementation.
     *
     * @return array|mixed
     */
    public function json()
    {
        if (null === $this->builder) {
            $this->builder = call_user_func($this->fn, Builder::new());
        }

        return ['method' => $this->method, 'params' => $this->builder->getQuery() ];
    }
}
