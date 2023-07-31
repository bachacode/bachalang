<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

use Bachalang\Token;

class FuncDefNode extends Node
{
    public function __construct(
        public ?Token $varNameToken,
        public $argNameTokens,
        public Node $bodyNode
    ) {
        if(!is_null($varNameToken)) {
            $this->posStart = $varNameToken->posStart;

        } elseif(count($argNameTokens) > 0) {
            $this->posStart = $argNameTokens[0]->posStart;
        } else {
            $this->posEnd = $bodyNode->posStart;
        }
        $this->posEnd = $bodyNode->posEnd;
    }
}
