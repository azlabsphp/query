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

use Drewlabs\Contracts\Support\Actions\Action as AbstractAction;
use Drewlabs\Contracts\Support\Actions\ActionResult as AbstractActionResult;

interface CommandInterface
{
    /**
     * Execute the DML Query action command on developper provided action interface.
     *
     * @return AbstractActionResult
     */
    public function __invoke(AbstractAction $action, \Closure $closure = null);

    /**
     * Execute the DML Query action command on developper provided action interface.
     *
     * @return AbstractActionResult
     */
    public function exec(AbstractAction $action, \Closure $closure = null);
}
