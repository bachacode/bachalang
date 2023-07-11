<?php

declare(strict_types=1);

namespace Bachalang;

class Token
{
    public function __construct(
        public string $type,
        public $value = null
    ) {
    }

    public function __toString(): string
    {
        if($this->value != null) {
            return "$this->type:$this->value";
        }
        return "$this->type";
    }
}
