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

use Drewlabs\Query\Contracts\FilterBagInterface;
use Drewlabs\Query\Contracts\Queryable as AbstractQueryable;
use Drewlabs\Query\Sanitizers\FilterBagSanitizer;

/**
 * @deprecated use `\Drewlabs\Query\Sanitizers\FilterBagSanitizer` instead
 * @internal
 */
final class PreparesFiltersBag
{
    /**
     * @var FilterBagInterface
     */
    private $instance;

    /**
     * creates class instances.
     *
     * @param FilterBagInterface $bag
     *
     * @return void
     */
    public function __construct($bag)
    {
        $this->instance = $bag;
    }

    /**
     * creates new class instance.
     *
     * @param FilterBagInterface $bag
     *
     * @return self
     */
    public static function new($bag)
    {
        return new static($bag);
    }

    /**
     * creates Query filters from parameter bag request.
     *
     * @param AbstractQueryable|\Closure(): AbstractQueryable|null $queryable
     *
     * @return array<string, mixed>
     */
    public function call($queryable = null, array $defaults = [])
    {
        $sanitizer = new FilterBagSanitizer($queryable);
        return $sanitizer->apply($this->instance, $defaults);
    }

    /**
     * @internal
     * 
     * @deprecated
     *
     * Build filters from parameter bags.
     *
     * **Note** It's an internal API implementation, do not use directly as the API might change
     *
     * @param FilterBagInterface|array $bag
     * @param array                    $defaults
     *
     * @return array<string, mixed>
     */
    public static function fromQueryParams(AbstractQueryable $queryable, $bag, $defaults = [])
    {
        $sanitizer = new FilterBagSanitizer($queryable);
        return $sanitizer->apply($bag, $defaults);
    }

    /**
     * @internal
     * 
     * @deprecated
     *
     * Build query filters using '_query' property of the parameter bag.
     *
     * **Note** It's an internal API implementation, do not use directly as the API might change
     *
     * @param FilterBagInterface|array $bag
     * @param array                    $output
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public static function fromBody($bag, $output = [])
    {
        $sanitizer = new FilterBagSanitizer(null);
        return $sanitizer->apply($bag, $output);
    }
}
