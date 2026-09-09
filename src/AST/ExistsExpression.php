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

final class ExistsExpression implements Expression
{
    /** @var string */
    private $property;

    /** @var array */
    private $query;

    /** @var string */
    private $name;

    public function __construct(string $property, array $query, string $name = 'exists')
    {
        $this->property = $property;
        $this->query = $query;
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
        return $this->query;
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
        if (empty($this->query)) {
            return [$this->name => [$this->property]];
        }

        return [ $this->name => [ $this->property, count($this->query) === 1 ? $this->query[0]->toExpression() : array_map(function ($expression) { return ['method' => $expression->getName(), 'params' => $expression->toExpression()]; }, $this->query) ] ];
    }

    public function toExpression()
    {
        if (empty($this->query)) {
            return [ 'column' => $this->property ];
        }

        return [ 'column' => $this->property, 'match' => count($this->query) === 1 ? $this->query[0]->toExpression() : array_map(function ($expression) { return ['method' => $expression->getName(), 'params' => $expression->toExpression()]; }, $this->query) ];
    }
}
