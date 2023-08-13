<?php

declare(strict_types=1);

namespace Bachalang\Nodes;

class IfNode extends Node
{
    public function __construct(
        public array $cases,
        public ?Node $elseCase,
    ) {
        $this->posStart = $cases[0][0]->posStart;
        $this->posEnd = ($elseCase ?? array_slice($cases, -1)[0][0])->posEnd;
    }
}
