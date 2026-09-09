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

use Drewlabs\Query\AST\Builder;
use Drewlabs\Query\Contracts\PreparesQuery;
use Drewlabs\Query\Contracts\Expression as AbstractExpression;

/**
 * @internal
 */
final class ExpressionFactory implements PreparesQuery
{
    /**
     * {@inheritDoc}
     *
     * @param string|array $params
     *
     * @return AbstractExpression
     */
    public function __invoke($params)
    {
        if (\is_array($params) && !empty($params)) {
            $method = $params['method'] ?? $params[key($params)];
            $args = $params['params'] ?? (\count($params) >= 2 ? array_slice(array_values($params), 1) : []);
            return new Expression($method, $args);
        }

        if (!\is_string($params)) {
            throw new \TypeError('expected method parameter to be an array or string, we got ' . (null !== $params && \is_object($params) ? $params::class : \gettype($params)));
        }

        $builder = new Builder;
        return $builder->build($params);
    }
}
