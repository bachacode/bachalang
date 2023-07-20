<?php

declare(strict_types=1);

namespace Bachalang;

class Context
{
    public function __construct(
        public $displayName,
        public $parent = null,
        public $parentEntryPos = null,
        public ?SymbolTable $symbolTable = null
    ) {
    }
}
