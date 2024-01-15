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

class QueryStatementsReducer
{
    /**
     * @var QueryStatement[]
     */
    private $statements;

    /**
     * Creates reducer instance
     * 
     * @param QueryStatement[] $statements 
     */
    public function __construct(array $statements)
    {
        $this->statements = $statements;
    }
    /**
     * Create new class instance
     * 
     * @param QueryStatement[] $statements 
     * @return static 
     */
    public static function new(array $statements)
    {
        return new static($statements);
    }

    /**
     * Reduce query statements on the filters builder instance
     * 
     * @param FiltersInterface $instance 
     * @param mixed $builder 
     * @return FiltersInterface 
     */
    public function call(FiltersInterface $instance, $builder)
    {
        // Compiles subquery into dictionnary case the subquery is a string or a list of values
        return array_reduce($this->statements, function ($carry, $statement) use ($builder) {
            // Prepare the query filters into the output variable to ensure method matches supported method
            $result = PreparesFiltersArray::doPrepare($statement->args(), $method = Filters::get($statement->method()));
            // Return the returned value of the function invokation on the query builder
            $builder = $carry->invoke($method, $builder, $result);

            // Return the filter builder instance for the iteration
            return $carry;
        }, $instance);
    }

}