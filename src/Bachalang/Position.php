<?php

declare(strict_types=1);

namespace Bachalang;

class Position
{
    public function __construct(
        public int $index,
        public int $line,
        public int $col,
        public string $fn,
        public string $ftxt
    ) {
    }

    public function advance(?string $currentChar = null): static
    {
        $this->index++;
        $this->col++;

        if($currentChar != null && strstr($currentChar, PHP_EOL)) {
            $this->line++;
            $this->col = 0;
        }
        return $this;
    }
}
