<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

use Bachalang\Token;

class CallNode extends Node
{
    public function __construct(
        public ?Node $nodeToCall,
        public array $argNodes,
    ) {

        $this->posStart = $nodeToCall->posStart;

        if(count($argNodes) > 0) {
            $this->posEnd = $argNodes[count($argNodes) - 1]->posEnd;
        } else {
            $this->posEnd = $nodeToCall->posEnd;
        }
    }
}
