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

/**
 * @internal
 */
final class PreparesOrderByQuery implements PreparesQuery
{
    public function __invoke($params)
    {
        if (\is_string($params)) {
            return ['by' => $params, 'order' => 'desc'];
        }
        if (!(array_keys($params) !== range(0, \count($params) - 1)) && !static::isKvPairList($params)) {
            return array_map(static function ($value) {
                return (new static())($value);
            }, $params);
        }
        if (!(isset($params['by']) || isset($params['column'])) && !isset($params['order'])) {
            throw new \InvalidArgumentException('orderBy query requires column and order keys');
        }
        $by = $params['column'] ?? ($params['by'] ?? 'updated_at');
        $order = $params['order'] ?? 'desc';

        return ['by' => $by, 'order' => (is_numeric($order) && $order < 0) || (strtolower((string)$order) === 'desc') ? 'desc' : 'asc'];
    }

    /**
     * Check if list is an associative list of list.
     *
     * @return bool
     */
    private static function isKvPairList(array $items)
    {
        if (empty($items)) {
            return false;
        }

        return 0 !== \count(array_filter(array_keys($items), 'is_string')) && array_filter($items, 'is_array') === $items;
    }
}
