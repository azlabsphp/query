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

use Drewlabs\Core\Helpers\Str;
use Drewlabs\Query\Contracts\PreparesQuery;
use Drewlabs\Query\Exceptions\MalformedQueryExpression;

/**
 * @internal
 */
class ExpressionFactory implements PreparesQuery
{
    /**
     * {@inheritDoc}
     *
     * @param string|array $params
     *
     * @return Expression[]
     */
    public function __invoke($params)
    {
        if (\is_array($params) && !empty($params)) {
            $method = $params['method'] ?? $params[key($params)];
            $args = $params['params'] ?? (\count($params) >= 2 ? array_slice(array_values($params), 1) : []);
            return [(new Expression($method, $args))];
        }

        if (!\is_string($params)) {
            throw new \TypeError('expected method parameter to be an array or string, we got ' . (null !== $params && \is_object($params) ? $params::class : \gettype($params)));
        }

        return array_map(function ($current) {
            return $this->buildASTExpression($current);
        }, explode('->', $params));
    }


    private function buildASTExpression(string $expression)
    {
        if (empty($method = Str::before('(', $expression))) {
            throw new MalformedQueryExpression($expression);
        }

        $arguments = Str::before(')', substr($expression, \strlen("$method(")));
        if (null === $arguments) {
            throw new MalformedQueryExpression($expression);
        }

        $arguments = trim($arguments);

        $args = array_map(static function ($p) {
            return trim($p);
        }, explode(',', $arguments));

        return new Expression($method, $args);
    }
}
