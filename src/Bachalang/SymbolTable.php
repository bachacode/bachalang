<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Values\Number;

class SymbolTable
{
    public function __construct(
        public array $symbols = [],
        public ?SymbolTable $parent = null
    ) {
    }

    public function get(string $name): Number
    {
        $value = $this->symbols[$name] ?? null;

        if($value == null && $this->parent != null) {
            return $this->parent->get($name);
        } else {
            return $value;
        }
    }

    public function set($name, $value)
    {
        $this->symbols[$name] = $value;
    }

    public function remove($name)
    {
        unset($this->symbols[$name]);
    }
}
