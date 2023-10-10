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

namespace Drewlabs\Query;

use Drewlabs\Query\Contracts\PreparesQuery;

class PreparesQueryStatement implements PreparesQuery
{
    /**
     * {@inheritDoc}
     *
     * @param string|array $params
     *
     * @return QueryStatement[]
     */
    public function __invoke($params)
    {
        if (\is_array($params) && !empty($params)) {
            $method = $params['method'] ?? $params[key($params)];
            $args = $params['params'] ?? (\count($params) >= 2 ? array_slice(array_values($params), 1) : []);
            return [(new QueryStatement($method, $args))];
        }

        // Case the params is not a string we throw a type error
        if (!\is_string($params)) {
            throw new \TypeError('Expected method parameter to be an array or string, we got ' . (null !== $params && \is_object($params) ? $params::class : \gettype($params)));
        }

        return array_reduce(explode('->', $params), function ($carry, $current) {
            $carry[] = QueryStatement::fromString($current);
            return $carry;
        }, []);
    }
}
