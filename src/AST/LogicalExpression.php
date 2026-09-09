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
use Drewlabs\Query\Sanitizers\Sanitizer;
use Override;

final class LogicalExpression implements Expression
{
    /** @var string */
    private $name;

    /** @var array */
    private $expressions;

    public function __construct(string $name, array $expressions)
    {
        $this->name = $name;
        $this->expressions = $expressions;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getParams()
    {
        return $this->expressions;
    }

    #[Override]
    public function apply(FiltersInterface $instance, $builder): FiltersInterface
    {
        $sanitizer = new Sanitizer($this->name);
        $instance->invoke($this->name, $builder, $sanitizer->apply($this->toExpression()));

        return $instance;
    }

    /** @return array<string, mixed>  */
    public function toDict()
    {
        return [ $this->name => count($this->expressions) === 1 ? $this->expressions[0]->toExpression() : array_map(function ($expression) { return $expression->toExpression(); }, $this->expressions) ];
    }

    public function toExpression()
    {
        return count($this->expressions) === 1 ? $this->expressions[0]->toExpression() : array_map(function ($expression) { return ['method' => $expression->getName(), 'params' => $expression->toExpression()]; }, $this->expressions);
    }
}
