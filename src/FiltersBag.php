<?php

namespace Drewlabs\Query;

use Drewlabs\Query\Contracts\InputBagInterface;

class FiltersBag implements InputBagInterface
{
    /**
     * Array of filters
     * 
     * @var array<string,array<string,mixed>|mixed>
     */
    private $values;

    /**
     * Creates class instance
     * 
     * @param array $values 
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Creates new filters bag instance
     * 
     * @param array $values 
     * @return static 
     */
    public static function new(array $values = [])
    {
        return new static($values ?? []);
    }

    public function clone()
    {
        return static::new($this->all());
    }

    public function has($name)
    {
        return array_key_exists($name, $this->values) && null !== $name;
    }

    public function get($name)
    {
        return $this->values[$name] ?? null;
    }

    public function all()
    {
        return array_merge(array_filter($this->values, function ($value) {
            return !is_null($value);
        }));
    }
}
