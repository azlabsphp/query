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

use Drewlabs\Core\Helpers\Iter;
use Drewlabs\Query\Contracts\CompilesQueryParameter;

/**
 * @internal
 */
class ConditionQuery implements CompilesQueryParameter
{
    public function compile($params)
    {
        $islist = array_filter($params, 'is_array') === $params;

        return $islist ? iterator_to_array(
            Iter::map(
                new \ArrayIterator($params),
                function ($item) {
                    return $this->list($item);
                }
            )
        ) : $this->list($params);
    }

    /**
     * Compile each element of the muti-dimensional array.
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    private function list(array $params)
    {
        $output = array_filter($params, static function ($item) {
            return null === $item || !isset($item);
        }) === $params;

        if ($output) {
            throw new \InvalidArgumentException('provided query parameters are not defined');
        }

        $params = array_values($params);

        $params[0] = \is_array($params[0]) && (isset($params[0]['model']) && $params[0]['column']) ? (new Attribute($params[0]))->__toString() : $params[0];

        return $params;
    }
}
