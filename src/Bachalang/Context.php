<?php

declare(strict_types=1);

namespace Bachalang;

class Context
{
    public function __construct(
        public string $displayName,
        public ?Context $parent = null,
        public ?Position $parentEntryPos = null,
        public ?SymbolTable $symbolTable = null
    ) {
    }
}
