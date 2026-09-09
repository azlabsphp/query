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

use Drewlabs\Query\Contracts\Expression;
use Drewlabs\Query\Contracts\FiltersInterface;
use Override;

final class InExpression implements Expression
{
    /** @var string */
    private $property;

    /** @var array */
    private $values;

    /** @var string */
    private $name;

    public function __construct(string $property, array $values, string $name = 'in')
    {
        $this->property = $property;
        $this->values = $values;
        $this->name = $name;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getParams()
    {
        return $this->values;
    }

    #[Override]
    public function apply(FiltersInterface $instance, $builder): FiltersInterface
    {
        $instance->invoke($this->name, $builder, $this->toExpression());

        return $instance;
    }

    public function toArray()
    {
        return [ 'method' => $this->name, 'params' => [ $this->property, $this->values ] ];
    }

    /** @return array<string, mixed>  */
    public function toDict()
    {
        return [ $this->name => [ $this->property, $this->values ] ];
    }

    public function toExpression()
    {
        return [ $this->property, $this->values ];
    }
}
