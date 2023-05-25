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

namespace Drewlabs\Query\Tests;

use Drewlabs\Query\Contracts\Queryable;

class Person implements Queryable
{
    public function getPrimaryKey()
    {
        return 'id';
    }

    public function getTable()
    {
        return 'persons';
    }

    public function getDeclaredColumns()
    {
        return [
            'firstname',
            'lastname',
            'phonenumber',
            'age',
            'sex',
            'is_active',
            'email',

            'addresses',
            'profile',
            'managers',
        ];
    }

    public function getDeclaredRelations()
    {
        return [
            'addresses',
            'profile',
            'managers',
        ];
    }
}
