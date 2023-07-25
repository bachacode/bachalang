<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

use Bachalang\Token;

class ForNode extends Node
{
    public function __construct(
        public Token $varNameToken,
        public Node $startValueNode,
        public Node $endValueNode,
        public ?Node $stepValueNode,
        public Node $bodyNode
    ) {
        $this->posStart = $varNameToken->posStart;
        $this->posEnd = $bodyNode->posEnd;
    }
}
