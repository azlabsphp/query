<?php

namespace Drewlabs\Query\Exceptions;

use Exception;

class MalformedSubQueryException extends Exception
{
    /**
     * Creates exception instance
     * 
     * @param string $query 
     * @return void 
     */
    public function __construct(string $query)
    {
        $message = 'Expect subquery syntax to be method(p1, p2, p3, ...), but ' . ($query) . ' was passed instead';
        parent::__construct($query);
    }
}