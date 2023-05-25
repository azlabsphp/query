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
 * Static class provides query filters definitions.
 */
class Filters
{
    /**
     * query filters dictionnary.
     *
     * @var array<string,string>
     */
    public const QUERY_FILTERS = [

        // and
        'and' => 'and',
        'where' => 'and',

        // or
        'or' => 'or',
        'orWhere' => 'or',
        'orwhere' => 'or',

        // in
        'in' => 'whereIn',
        'whereIn' => 'whereIn',
        'wherein' => 'whereIn',

        //  not in
        'notIn' => 'notIn',
        'notin' => 'notIn',
        'whereNotIn' => 'notIn',
        'wherenotin' => 'notIn',

        // null
        'isNull' => 'isNull',
        'isnull' => 'isNull',
        'whereNull' => 'isNull',
        'wherenull' => 'isNull',

        // or null
        'orIsNull' => 'orIsNull',
        'orisnull' => 'orIsNull',
        'orWhereNull' => 'orIsNull',
        'orwherenull' => 'orIsNull',

        // not null
        'notNull' => 'notNull',
        'notnull' => 'notNull',
        'whereNotNull' => 'notNull',
        'wherenotnull' => 'notNull',

        // or not null
        'orNotNull' => 'orNotNull',
        'ornotnull' => 'orNotNull',
        'orWhereNotNull' => 'orNotNull',
        'orwherenotnull' => 'orNotNull',

        // exists
        'exists' => 'exists',
        'has' => 'exists',
        'whereHas' => 'exists',
        'wherehas' => 'exists',

        // not exists
        'notExists' => 'notExists',
        'notexists' => 'notExists',
        'whereDoesntHave' => 'notExists',
        'wheredoesnthave' => 'notExists',
        'doesnthave' => 'notExists',

        // sort
        'sort' => 'sort',
        'orderBy' => 'sort',
        'orderby' => 'sort',

        // date
        'date' => 'date',
        'whereDate' => 'date',
        'wheredate' => 'date',

        // or date
        'orDate' => 'orDate',
        'ordate' => 'orDate',
        'orWhereDate' => 'orDate',
        'orwheredate' => 'orDate',

        // between
        'between' => 'between',
        'whereBetween' => 'between',
        'wherebetween' => 'between',

        // group
        'group' => 'group',
        'groupBy' => 'group',
        'groupby' => 'group',

        // join
        'join' => 'join',
        'rightjoin' => 'rightJoin',
        'leftjoin' => 'leftJoin',

        // Limit query
        'limit' => 'limit',
    ];

    /**
     * Get the filter matching the `$name` parameter.
     *
     * @return string
     */
    public static function get(string $name)
    {
        return self::QUERY_FILTERS[$name] ?? $name;
    }

    /**
     * Check is `$name` exists in supported query filters.
     *
     * @return bool
     */
    public static function exists(string $name)
    {
        return false !== array_search($name, array_keys(self::QUERY_FILTERS), true);
    }
}
