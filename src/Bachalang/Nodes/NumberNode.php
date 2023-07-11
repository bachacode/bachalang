<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

use Bachalang\Token;

class NumberNode
{
    public function __construct(
        public Token $token
    ) {
    }

    public function __toString(): string
    {
        return "{$this->token}";
    }
}
