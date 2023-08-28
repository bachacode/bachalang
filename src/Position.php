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
        public string $ftxt,
        public int $counter = 0,
    ) {
    }

    public function advance(?string $currentChar = null): static
    {
        $this->index++;
        $this->col++;

        if($currentChar != null && str_contains(PHP_EOL, $currentChar)) {
            $this->counter++;
            if($this->counter == 2) {
                $this->line++;
                $this->col = 0;
                $this->counter = 0;
            }
        }
        return $this;
    }
}
