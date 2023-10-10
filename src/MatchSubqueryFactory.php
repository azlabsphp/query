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

use Closure;
use Drewlabs\Query\Contracts\FiltersInterface;

class MatchSubqueryFactory
{
    /**
     * Create new class instance
     * 
     * @return static 
     */
    public static function new()
    {
        return new static;
    }

    /**
     * Create a match subquery closure
     * 
     * @param mixed $arguments 
     * @return Closure(FiltersInterface $instance, mixed $builder): FiltersInterface 
     */
    public function create($arguments)
    {
        return static function (FiltersInterface $instance, $builder) use ($arguments) {
            // Compiles subquery into dictionnary case the subquery is a string or a list of values
            $statements = (new PreparesQueryStatement())->__invoke($arguments);
            return QueryStatementsReducer::new($statements)->call($instance, $builder);
        };
    }
}