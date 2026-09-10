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
use Override;
use Drewlabs\Query\Contracts\FiltersInterface;

final class ChainedExpression implements Expression
{
    /** @var array */
    private $expressions;

    public function __construct(array $expressions)
    {
        $this->expressions = $expressions;
    }

    #[Override]
    public function getName(): string
    {
        return 'CHAIN';
    }

    #[Override]
    public function getParams()
    {
        return $this->expressions;
    }

    #[Override]
    public function apply(FiltersInterface $instance, $builder): FiltersInterface
    {
        return array_reduce($this->expressions, function ($carry, $expression) use ($builder) { return $expression->apply($carry, $builder); }, $instance);
    }

    /** @return array<string, mixed>  */
    public function toDict()
    {
        $items = [];
        $tracking = [];

        foreach ($this->expressions as $item) {
            $name = $item->getName();

            if (isset($tracking[$name])) {
                $value = $items[$name];
                $expression = $item->toExpression();

                if ($tracking[$name] > 1) {
                    array_push($value, $expression);
                } else {
                    $value = [$value, $item->toExpression()];
                }

                $items[$name] = $value;
                $tracking[$name] += 1;
                
                continue;
            }
            
            $tracking[$name] = 1;
            $items[$name] = $item->toExpression();

        }

        return $items;
    }

    public function toExpression()
    {
        return array_map(function ($expression) { return [ 'method' => $expression->getName(), 'params' => $expression->toExpression() ]; }, $this->expressions);
    }
}
