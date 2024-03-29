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

/**
 * @internal
 */
class PreparesFiltersArray
{
    /**
     * @var array<string,array<string,mixed>|mixed>
     */
    private $values;

    /**
     * Creates class instance.
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Creates new class instance.
     *
     * @return static
     */
    public static function new(array $values = [])
    {
        return new static($values);
    }

    /**
     * Prepares a array of filters by mapping filter methods from input source
     * and validating filters parameters.
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    public function call()
    {
        $output = [];
        static::prepareInto($output);

        return $output;
    }

    /**
     * @internal
     *
     * Map query filters into the `$output` array
     *
     * **Note** It's an internal API implementation, do not use directly as the API might change
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public function prepareInto(array &$output)
    {
        foreach ($this->values as $key => $value) {
            // Initialize the result array
            $results = [];

            // We search for the query key matches in the supported query methods
            if (Filters::exists($key)) {
                // get the query filters for the current key and set the key value to the resolved value
                $results = static::doPrepare($value, $key = Filters::get($key));
            }

            // In case the buildParameters() returns an empty result we simply ignore the provided
            // query method
            if (empty($results)) {
                continue;
            }

            // We try to merge the current query parameters into existing parameters
            // if they exist in the filters
            if (isset($output[$key])) {
                if (array_filter($results, 'is_array') === $results) {
                    foreach ($results as $current) {
                        $output[$key][] = $current;
                    }
                } else {
                    $output[$key][] = $results;
                }
                continue;
            }

            if (!\is_array($results)) {
                $output[$key] = $results;
                continue;
            }

            // Default case
            $output[$key] = array_merge($output[$key] ?? [], $results);
        }
    }

    /**
     * @internal
     *
     * Build queries based on list of query parameters
     *
     * @param array|string|mixed $params
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public static function doPrepare($params, string $method)
    {
        switch ($method) {
            // Default group
            case 'and':
            case 'date':
            case 'orDate':
            case 'or':
                return (new PreparesBaseQuery())($params);
                // Exists group
            case 'exists':
            case 'orExists':
            case 'notExists':
            case 'orNotExists':
                return (new PreparesExistQuery())($params);
                // In group
            case 'in':
            case 'notIn':
                return (new PreparesInQuery())($params);
                // Sort group
            case 'sort':
                return (new PreparesOrderByQuery())($params);

                // Null group
            case 'isNull':
            case 'orIsNull':
            case 'notNull':
            case 'orNotNull':
                return (new PreparesNullQuery())($params);
                // case 'between':
                // case 'group':
                // case 'join':
                // case 'limit':
            default:
                return $params;
        }
    }
}
