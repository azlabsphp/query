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

namespace Drewlabs\Query\Exceptions;

class MalformedQueryExpression extends QueryException
{
    /**
     * Creates exception instance.
     */
    public function __construct(string $query)
    {
        $message = 'Expect query expression syntax to be method(p1, p2, p3, ...), but '.$query.' was passed instead';
        parent::__construct($message);
    }
}
