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

namespace Drewlabs\Query\AST;


final class Condition
{
    /** @var string */
    private $name;

    /** @var array */
    private $op;

    /** @var string */
    private $value;

    /**
     * @param string $name 
     * @param string $op 
     * @param mixed $value 
     */
    public function __construct(string $name, string $op, $value)
    {
        $this->name = $name;
        $this->op = $op;
        $this->value = $value;
    }

    public function toExpression()
    {
        return [$this->name, $this->op, $this->value];
    }


    public function toDict()
    {
        return $this->toExpression();
    }
}
