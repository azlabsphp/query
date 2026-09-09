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

namespace Drewlabs\Query\Sanitizers;

use Drewlabs\Query\Filters;

final class Sanitizer
{
    /** @var string */
    private $name;


    public function __construct(string $name)
    {
        $this->name = Filters::get($name);
    }

    /**
     * returns the sanitizer name
     * 
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * calls the sanitizer dedicated to the name parameter on the input parameter
     * 
     * @param mixed $params
     * 
     * @return string|int|array|mixed 
     */
    public function apply($params)
    {
        $name = strtolower($this->name);
        switch ($name) {
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