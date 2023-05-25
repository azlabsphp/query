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

class JoinQuery implements CompilesQueryParameter
{
    public function compile($params)
    {
        $list = array_filter($params, 'is_array') === $params;

        return $list ? iterator_to_array(
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
        $filterResult = array_filter($params, static function ($item) {
            return null === $item;
        }) === $params;
        if ($filterResult) {
            throw new \InvalidArgumentException('Provided query parameters are not defined');
        }
        // Insure that where not working with associative arrays
        $params = array_values($params);
        // Case the operator part if missing
        if (3 === \count($params)) {
            $params[0] = (\is_string($params[0]) && !class_exists($params[0])) ? $params[0] : (string) (new Attribute(
                \is_array($params[0]) ? $params[0] : ['model' => $params[0]]
            ));
            $params[1] = \is_string($params[1]) ? $params[1] : (string) (new Attribute($params[1]));
            $params[2] = \is_string($params[2]) ? $params[2] : (string) (new Attribute($params[2]));
            array_splice($params, 2, 1, ['=', $params[2]]);
        } else {
            $params[0] = (\is_string($params[0]) && !class_exists($params[0])) ? $params[0] : (new Attribute(
                \is_array($params[0]) ? $params[0] : ['model' => $params[0]]
            ));
            $params[1] = \is_string($params[1]) ? $params[1] : (string) (new Attribute($params[1]));
            $params[3] = \is_string($params[3]) ? $params[3] : (string) (new Attribute($params[3]));
        }

        return $params;
    }
}
