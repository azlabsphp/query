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

use Drewlabs\Collections\Collection;
use Drewlabs\Query\Contracts\EnumerableResultInterface;

class EnumerableResult implements EnumerableResultInterface, \JsonSerializable
{
    /**
     * @var mixed
     */
    private $values;

    /**
     * Creates class instance.
     *
     * @param mixed $items
     */
    public function __construct($items = null)
    {
        $items = $items ?? [];
        $this->setCollection($items);
    }

    public function __call($name, $arguments)
    {
        if (!\is_object($values = $this->getCollection())) {
            throw new \BadMethodCallException('Method does not exists on class '.__CLASS__);
        }

        return $this->proxy($values, $name, $arguments);
    }

    public function getCollection()
    {
        return $this->offsetGet('data');
    }

    public function items()
    {
        return $this->offsetGet('data');
    }

    public function setCollection($items)
    {
        if (\is_array($items)) {
            $items = Collection::make($items);
        }
        $this->offsetSet('data', $items);

        return $this;
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return \array_key_exists($offset, $this->values) && isset($this->values[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return \array_key_exists($offset, $this->values) ? $this->values[$offset] : null;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        $this->values[$offset] = $value;
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        unset($this->values[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->values;
    }

    // #region Miscellanous
    private function proxy($object, $method, $args = [], \Closure $default = null)
    {
        try {
            // Call the method on the provided object
            return $object->{$method}(...$args);
        } catch (\Error|\BadMethodCallException $e) {
            // Call the default method if the specified method does not exits
            if ((null !== $default) && \is_callable($default)) {
                return $default(...$args);
            }
            $pattern = '~^Call to undefined method (?P<class>[^:]+)::(?P<method>[^\(]+)\(\)$~';
            if (!preg_match($pattern, $e->getMessage(), $matches)) {
                throw $e;
            }
            if ($matches['class'] !== $object::class || $matches['method'] !== $method) {
                throw $e;
            }
            throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s()', static::class, $method));
        }
    }
    // #region Miscellanous
}
