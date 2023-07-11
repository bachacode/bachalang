<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

class NumberNode
{
    public function __construct(
        public $token
    ) {
    }

    public function __toString(): string
    {
        return "{$this->token}";
    }
}
