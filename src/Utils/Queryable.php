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

namespace Drewlabs\Query\Utils;

use Drewlabs\Query\Contracts\Queryable as AbstractQueryable;

class Queryable implements AbstractQueryable
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var array<string>
     */
    private $columns;

    /**
     * @var array<string>
     */
    private $relations;

    /**
     * @var string
     */
    private $table;

    /**
     * Creates class instances.
     *
     * @param array $columns
     */
    public function __construct(string $id = 'id', $columns = [], array $relations = [], string $table = null)
    {
        $this->id = $id ?? 'id';
        $this->columns = $columns ?? [];
        $this->relations = $relations ?? [];
        $this->table = $table;
    }

    /**
     * Creates new instance of queryable from a source queryable.
     *
     * @return static
     */
    public static function copy(AbstractQueryable $queryable)
    {
        return new static($queryable->getPrimaryKey(), $queryable->getDeclaredColumns(), $queryable->getDeclaredRelations(), $queryable->getTable());
    }

    public function getPrimaryKey()
    {
        return $this->id;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getDeclaredColumns()
    {
        return $this->columns ?? [];
    }

    public function getDeclaredRelations()
    {
        return $this->relations ?? [];
    }
}
