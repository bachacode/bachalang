<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

use Bachalang\Token;

class BinOpNode
{
    public function __construct(
        public NumberNode $leftNode,
        public Token $opNode,
        public BinOpNode|NumberNode $rightNode
    ) {
    }

    public function __toString(): string
    {
        return "{$this->leftNode}, {$this->opNode}, {$this->rightNode}";
    }
}
