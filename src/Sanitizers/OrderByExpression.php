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

use BadMethodCallException;
use Drewlabs\Core\Helpers\Arr;
use Drewlabs\Query\Contracts\PreparesQuery;
use InvalidArgumentException;

/**
 * @internal
 */
final class OrderByExpression implements PreparesQuery
{
    public function __invoke($params)
    {
        if (\is_string($params)) {
            return ['by' => $params, 'order' => 'desc'];
        }

        if (!is_array($params) || (is_array($params) && empty($params))) {
            throw new BadMethodCallException('sort query expect the column name as string or a dictionnary of column and order');
        }

        if (!Arr::isassoc($params) && !Arr::isassoclist($params)) {
            return array_map(static function ($value) {
                return (new static())($value);
            }, $params);
        }

        if (!(isset($params['by']) || isset($params['column'])) && !isset($params['order'])) {
            throw new InvalidArgumentException('sort query expects a column and order properties');
        }

        $by = $params['column'] ?? $params['by'];
        $order = $params['order'] ?? 'desc';
        
        return ['by' => $by, 'order' => (is_numeric($order) && $order < 0) || (strtolower((string)$order) === 'desc') ? 'desc' : 'asc'];
    }
}
