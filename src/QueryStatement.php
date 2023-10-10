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

use Drewlabs\Query\Exceptions\MalformedQueryExpression;

class QueryStatement
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $args;

    /**
     * Create new query statement instance.
     *
     * @param array $args
     */
    public function __construct(string $method, array $args)
    {
        $this->method = $method;
        $this->args = $args;
    }

    /**
     * Create instance from string
     * 
     * @param string $statement 
     * @return QueryStatement 
     * @throws MalformedQueryExpression 
     */
    public static function fromString(string $statement)
    {
        // Case the parameters is not an array type, we parse the string in the format method(p1, p2, p3, ...)
        if (empty($method = static::strBefore('(', $statement))) {
            throw new MalformedQueryExpression($statement);
        }

        $arguments = static::strBefore(')', substr($statement, \strlen("$method(")));
        // Check if the query parameters is empty
        if (null === $arguments) {
            throw new MalformedQueryExpression($statement);
        }

        // Parse the query arguments
        $arguments = trim($arguments);

        // Explode arguments by comma sepatator
        $args = array_map(static function ($p) {
            return trim($p);
        }, explode(',', $arguments));

        return new QueryStatement($method, $args);
    }

    /**
     * Return the query method
     * 
     * @return string 
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * Returns the list statement arguments
     * 
     * @return array 
     */
    public function args()
    {
        return $this->args ?? [];
    }

    /**
     * Call the query state on the query driver
     * 
     * @param object $driver 
     * @return mixed 
     */
    public function call(object $driver)
    {
        return call_user_func_array([$driver, $this->method()], $this->args());
    }

    /**
     * Returns the array representation of the statement.
     *
     * @return (string|array)[]
     */
    public function toArray()
    {
        return ['method' => trim($this->method), 'params' => $this->args()];
    }

    /**
     * Query string before a given character.
     *
     * @return string
     */
    private static function strBefore(string $character, string $haystack)
    {
        if ($pos = strpos($haystack, $character)) {
            return substr($haystack, 0, $pos);
        }

        return null;
    }
}
