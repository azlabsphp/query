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

/**
 * @internal
 */
final class ExistsExpression implements PreparesQuery
{
    public function __invoke($params)
    {
        if (empty($params)) {
            return $params;
        }

        if (is_string($params)) {
            return [$params];
        }

        if (!is_array($params)) {
            throw new BadMethodCallException('exists query expect string or array<string, mixed>');
        }

        return $this->sanitize($params);
    }


    private function sanitize(array $params)
    {

        if (!Arr::isassoc($params) && Arr::isList($params)) {
            return array_reduce($params, function (array $carry, array $current) {
                if (empty($current)) {
                    return $carry;
                }
                $carry[] = $this->sanitizeSubquery($current);

                return $carry;
            }, []);
        }

        return $this->sanitizeSubquery($params);
    }

    /**
     * prepares subquery parameters.
     *
     * @param array $value
     *
     * @throws \Exception
     *
     * @return array
     */
    private function sanitizeSubquery(array $value)
    {
        if (null === ($column = $value['column'] ?? $value[key($value)])) {
            throw new \Exception('bad sub query, column property is required');
        }

        $match = $value['match'] ?? (\count($value) >= 2 ? array_values($value)[1] : null);

        return $match ? [$column, Subquery::new()->create($match)] : [$column];
    }
}
