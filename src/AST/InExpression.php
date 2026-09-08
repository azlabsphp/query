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

final class InExpression
{
    /** @var string */
    private $name;

    /** @var array */
    private $values;

    /** @var string */
    private $method;

    public function __construct(string $name, array $values, string $method = 'in')
    {
        $this->name = $name;
        $this->values = $values;
        $this->method = $method;
    }

    public function toArray()
    {
        return ['method' => $this->method, 'params' => [$this->name, $this->values]];
    }
}
