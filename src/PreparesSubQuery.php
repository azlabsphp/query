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
class PreparesSubQuery implements PreparesQuery
{
    public function __invoke($params)
    {
        // Case the query parameters is empty, return the parameters as it's
        if (empty($params)) {
            return $params;
        }
    
        if (!($isKvPair = $this->isKvPair($params)) && ((array_filter($params, 'is_array') === $params) && !$isKvPair)) {
            return array_reduce($params, function (array $carry, array $current) {
                if (empty($current)) {
                    return $carry;
                }

                // Compile query parameters
                $carry[] = $this->prepareSubQueryParams($current);

                return $carry;
            }, []);
        }

        return $this->prepareSubQueryParams($params);
    }

    /**
     * Checks if an array is a key value pair array.
     *
     * @return bool
     */
    private function isKvPair(array $value)
    {
        return \is_array($value) && array_keys($value) !== range(0, \count($value) - 1);
    }

    /**
     * Prepares subquery parameters.
     *
     * @param mixed $value
     *
     * @throws \Exception
     *
     * @return array
     */
    private function prepareSubQueryParams($value)
    {
        if (null === ($column = $value['column'] ?? $value[key($value)])) {
            throw new \Exception('Bad sub query, column is not provided');
        }
        $match = $value['match'] ?? (\count($value) >= 2 ? array_values($value)[1] : null);

        // Returns the compiled query array
        return $match ? [$column, MatchSubqueryFactory::new()->create($match)] : [$column];
    }
}
