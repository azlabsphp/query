<?php

namespace Drewlabs\Query\Exceptions;

class MalformedSubQueryException extends QueryException
{
    /**
     * Creates exception instance
     * 
     * @param string $query 
     */
    public function __construct(string $query)
    {
        $message = 'Expect subquery syntax to be method(p1, p2, p3, ...), but ' . ($query) . ' was passed instead';
        parent::__construct($message);
    }
}