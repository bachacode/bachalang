<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Values\Value;

class SymbolTable
{
    public function __construct(
        public array $symbols = [],
        public ?SymbolTable $parent = null
    ) {
    }

    public function &get(string $name): ?Value
    {
        $value = &$this->symbols[$name] ?? null;

        if($value == null && $this->parent != null) {
            $var = &$this->parent->get($name);
            return $var;
        } else {
            return $value;
        }
    }

    public function set(string $name, mixed $value): void
    {
        $this->symbols[$name] = $value;
    }

    public function remove(string $name): void
    {
        unset($this->symbols[$name]);
    }
}
