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

final class ExistsExpression
{
    /** @var string */
    private $column;

    /** @var array */
    private $query;

    /** @var string */
    private $method;

    public function __construct(string $column, array $query, string $method = 'exists')
    {
        $this->column = $column;
        $this->query = $query;
        $this->method = $method;
    }


    public function toArray()
    {
        if (empty($this->query)) {
            return ['method' => $this->method, 'column' => $this->column];
        }

        return ['method' => $this->method, 'column' => $this->column, 'match' => count( $this->query) === 1 ? $this->query[0]->toArray() : array_map(function ($expression) {
            return $expression->toArray();
        }, $this->query)];
    }
}
