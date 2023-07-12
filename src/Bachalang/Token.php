<?php

declare(strict_types=1);

namespace Bachalang;

class Token
{
    public ?Position $posStart = null;
    public ?Position $posEnd = null;

    public function __construct(
        public string $type,
        Position $posStart,
        ?Position $posEnd = null,
        public $value = null
    ) {
        if(!is_null($posStart)) {
            $this->posStart = $posStart->copy();
            $this->posEnd = $posStart->copy();
            $this->posEnd->advance();
        }

        if(!is_null($posEnd)) {
            $this->posEnd = $posEnd->copy();
        }
    }

    public function __toString(): string
    {
        if($this->value != null) {
            return "$this->type:$this->value";
        }
        return "$this->type";
    }
}
