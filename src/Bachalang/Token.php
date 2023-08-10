<?php

declare(strict_types=1);

namespace Bachalang;

class Token
{
    public ?Position $posStart = null;
    public ?Position $posEnd = null;

    public function __construct(
        public TokenType $type,
        Position $posStart,
        ?Position $posEnd = null,
        public mixed $value = null
    ) {
        if(!is_null($posStart)) {
            $this->posStart = clone $posStart;
            $this->posEnd = clone $posStart;
            $this->posEnd->advance();
        }

        if(!is_null($posEnd)) {
            $this->posEnd = clone $posEnd;
        }
    }

    public function matches(TokenType $type, ?string $value): bool
    {
        return $this->type == $type && $this->value == $value;
    }

    public function __toString(): string
    {
        $tokenType = $this->type->value;
        if($this->value != null) {
            return "$tokenType:$this->value";
        }
        return $tokenType;
    }
}
