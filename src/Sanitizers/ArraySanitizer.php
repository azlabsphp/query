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

final class ArraySanitizer
{
    public function apply(array $values)
    {
        $output = [];
        
        foreach ($values as $name => $value) {
            if (!Filters::exists($name)) {
                continue;
            }

            $sanitizer = new Sanitizer($name);
            if (empty($result = $sanitizer->apply($value))) {
                continue;
            }

            $output[$sanitizer->getName()] = $result;
        }

        return $output;
    }
}
