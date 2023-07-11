<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

class BinOpNode
{
    public function __construct(
        public NumberNode $leftNode,
        public string $opNode,
        public NumberNode $rightNode
    ) {
    }

    public function __toString(): string
    {
        return "{$this->leftNode}, {$this->opNode}, {$this->rightNode}";
    }
}
