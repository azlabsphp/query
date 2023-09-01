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

use Drewlabs\Query\Contracts\FiltersInterface;
use Drewlabs\Query\Contracts\PreparesQuery;

class PreparesSubQuery implements PreparesQuery
{
    public function __invoke($params)
    {
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
     * Creates a factory function that get call on query filters.
     *
     * @param mixed $query
     *
     * @return \Closure(mixed $q): mixed
     */
    public function subQueryFactory($query)
    {
        return static function (FiltersInterface $instance, $builder) use ($query) {
            // Compiles subquery into dictionnary case the subquery is a string or a list of values
            $statements = (new PreparesMatchQuery())->__invoke($query);
            return array_reduce($statements, function($carry, $statement) use ($builder) {
                // Prepare the query filters into the output variable to ensure method matches supported method
                $result = PreparesFiltersArray::doPrepare($statement->args(), $method = Filters::get($statement->method()));
                // Return the returned value of the function invokation on the query builder
                return $carry->invoke($method, $builder, $result);
            }, $instance);
        };
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
        return $match ? [$column, $this->subQueryFactory($match)] : [$column];
    }
}
