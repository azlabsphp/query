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

namespace Drewlabs\Query\Contracts;

interface PaginateResultInterface
{
    /**
     * The URL for the next page, or null.
     *
     * @return string|null
     */
    public function getNextPageUrl();

    /**
     * Get the URL for the previous page, or null.
     *
     * @return string|null
     */
    public function getPreviousPageUrl();

    /**
     * returns the list of items.
     *
     * @psalm-template TResult
     *
     * @return TResult
     */
    public function items();

    /**
     * return the current page being paginated.
     *
     * @return int
     */
    public function getCurrentPage();

    /**
     * returns the total item per page.
     *
     * @return int
     */
    public function getItemPerPage();

    /**
     * returns the base path for the pagination generated url.
     *
     * @return string
     */
    public function getPath();

    /**
     * Get the URL fragment to be appended to URLs.
     *
     * @return mixed
     */
    public function getFragment();
}
