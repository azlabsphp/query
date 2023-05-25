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

use Drewlabs\Core\Helpers\Functional;
use Drewlabs\Query\Contracts\PreparesQuery;

class PreparesNullQuery implements PreparesQuery
{
    public function __invoke($params)
    {
        $closure = Functional::memoize(static function (array $value) use (&$closure) {
            if (\is_string($value)) {
                return $value;
            }
            if (!($isKvPair = array_keys($value) !== range(0, \count($value) - 1))) {
                return array_reduce(
                    $value,
                    static function ($carry, $result) use (&$closure) {
                        if (\is_array($result) && array_keys($result) !== range(0, \count($result) - 1)) {
                            $result = $closure($result);
                        }

                        return \in_array($result, $carry, true) ? $carry : array_merge($carry, \is_array($result) ? $result : [$result]);
                    },
                    []
                );
            }
            if ($isKvPair && !isset($value['column'])) {
                throw new \InvalidArgumentException('sort query requires column key');
            }

            return $value['column'] ?? $value[0] ?? null;
        });

        // Prepare query filters parameter
        $prepare = static function ($value) use ($closure) {
            return \is_string($value) ? $value : $closure($value);
        };

        // Cleanup query filters removing null result
        $cleanup = static function ($array) {
            if (\is_array($array)) {
                return array_filter($array, static function ($item) {
                    return null !== $item;
                });
            }

            return $array;
        };
        if (\is_array($params)) {
            if (array_filter($params, 'is_array') === $params) {
                return $cleanup(array_reduce($params, static function ($carry, $current) use ($prepare) {
                    $result = $prepare($current);
                    if (\in_array($result, $carry, true)) {
                        return $carry;
                    }

                    return array_merge($carry, \is_array($result) ? $result : [$result]);
                }, []));
            } else {
                return $cleanup($closure($params));
            }
        }

        return $cleanup($prepare($params));
    }
}
