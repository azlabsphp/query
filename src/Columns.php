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

final class Columns
{
    /** @var array<string> */
    private $value;

    /**
     * creates class instance.
     */
    public function __construct(array $columns)
    {
        $this->value = $columns;
    }

    /**
     * creates new class instance.
     *
     * @param array $columns
     *
     * @return self
     */
    public static function new($columns = ['*'])
    {
        return new static($columns ?? ['*']);
    }

    /**
     * convert user provided selectable columns in a tuple of $columns and $relations to load.
     *
     * @return array<string[]>
     */
    public function tuple(array $declared = [], array $relations = [])
    {
        $values = [];

        $this->flatten($this->value, $values);

        $map = array_map(static function ($item) {
            return str_contains($item, '.') ? Str::before('.', $item) : $item;
        }, $relations ?? []);

        $includes = array_filter($values, static function ($item) use ($map, $relations) {
            if (str_contains($item, '.')) {
                return \in_array(Str::before('.', $item), $map, true) || \in_array($item, $relations, true);
            }

            return \in_array($item, $map, true);
        });

        $columns = array_intersect($values, $declared);
        $columns = \in_array('*', $values, true) ? [] : (empty($value = array_diff($columns, $includes)) ? [null] : $value);

        return [array_values($columns), array_values($includes)];
    }

    /**
     * convert the iterable list into 1-dimensional array.
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
