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

use Drewlabs\Core\Helpers\Str;

class Columns
{
    /**
     * @var array<string>
     */
    private $value;

    /**
     * Creates class instance.
     */
    public function __construct(array $columns)
    {
        $this->value = $columns;
    }

    /**
     * Creates new class instance.
     *
     * @param array $columns
     *
     * @return self
     */
    public static function new($columns = ['*'])
    {
        return new self($columns ?? ['*']);
    }

    /**
     * Convert user provided selectable columns in a tuple of $columns and $relations to load.
     *
     * @return array<string[]>
     */
    public function tuple(array $declared = [], array $relations = [])
    {
        $values = [];

        // we convert the list of columns into 1-dimensional tableau
        $this->flatten($this->value, $values);

        // Get the list of top level declared relations
        $mapResult = array_map(static function ($item) {
            return str_contains($item, '.') ? Str::before('.', $item) : $item;
        }, $relations ?? []);
        // Creates the list of relation fields to be added to the model list of columns
        $filterResult = array_filter($values, static function ($item) use ($mapResult, $relations) {
            if (str_contains($item, '.')) {
                return \in_array(Str::before('.', $item), $mapResult, true) || \in_array($item, $relations, true);
            }

            return \in_array($item, $mapResult, true);
        });
        // Create the actual list of model column to be selected from the database
        $columns = array_intersect($values, $declared);
        if (\in_array('*', $values, true)) {
            $columns = [];
        } else {
            $columns = empty($value = array_diff($columns, $filterResult)) ? [null] : $value;
        }
        // Return the tuple of column and relations
        return [array_values($columns), array_values($filterResult)];
    }

    /**
     * Convert the iterable list into 1-dimensional array.
     *
     * @param array $values
     *
     * @return void
     */
    private function flatten(iterable $values, array &$output)
    {
        array_walk_recursive($values, static function ($value) use (&$output) {
            $output[] = $value;
        });
    }
}
