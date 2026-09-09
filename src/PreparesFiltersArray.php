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

use Drewlabs\Query\Sanitizers\{LogicalExpression, ExistsExpression, InExpression, NullExpression, OrderByExpression};

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
     * @throws \InvalidArgumentException
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
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function prepareInto(array &$output)
    {
        foreach ($this->values as $key => $value) {
            $results = [];

            if (Filters::exists($key)) {
                $results = static::doPrepare($value, $key = Filters::get($key));
            }

            if (empty($results)) {
                continue;
            }

            if (isset($output[$key])) {
                if (array_filter($results, 'is_array') === $results) {
                    array_push($output[$key], ...$results);
                    // foreach ($results as $current) {  $output[$key][] = $current;  }
                } else {
                    $output[$key][] = $results;
                }
                continue;
            }

            if (!\is_array($results)) {
                $output[$key] = $results;
                continue;
            }

            $output[$key] = array_merge($output[$key] ?? [], $results);
        }
    }

    /**
     * @internal
     *
     * build queries based on list of query parameters
     *
     * @param array|string|mixed $params
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public static function doPrepare($params, string $method)
    {
        $method = strtolower($method);
        switch ($method) {
            case 'and':
            case 'date':
            case 'ordate':
            case 'or':
                return (new LogicalExpression())($params);
            case 'exists':
            case 'orexists':
            case 'notexists':
            case 'ornotexists':
                return (new ExistsExpression())($params);
            case 'in':
            case 'notin':
                return (new InExpression())($params);
            case 'sort':
                return (new OrderByExpression())($params);
            case 'isnull':
            case 'orisnull':
            case 'notnull':
            case 'ornotnull':
                return (new NullExpression())($params);
            default:
                return $params;
        }
    }
}
