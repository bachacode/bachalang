<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Errors\IllegalCharError;

class Lexer
{
    private Position $pos;
    public Position $posStart;
    public Position $posEnd;

    public function __construct(
        private string $fn,
        private string $text,
        private ?string $currentChar = null,
    ) {

        $this->pos = new Position(-1, 0, -1, $fn, $this->text);
        $this->advance();
    }

    private function advance(): void
    {
        $this->pos->advance($this->currentChar);
        if($this->pos->index < strlen($this->text)) {
            $this->currentChar = $this->text[$this->pos->index];
        } else {
            $this->currentChar = null;
        }
    }

    public function makeTokens(): string|array
    {
        $tokens = [];

        while ($this->currentChar != null) {
            if(ctype_space($this->currentChar)) {
                $this->advance();
            } elseif (TT::checkToken(($this->currentChar))) {
                array_push($tokens, new Token(TT::getToken($this->currentChar), $this->pos));
                $this->advance();
            } elseif(str_contains(DIGITS, $this->currentChar)) {
                array_push($tokens, $this->makeNumber());
            } else {
                $posStart = $this->pos->copy();
                $char = $this->currentChar;
                $this->advance();
                $errorMessage = (string) new IllegalCharError(
                    $posStart,
                    $this->pos,
                    "the following character is not permited >> {$char}"
                );
                return "ERROR: {$errorMessage}" . PHP_EOL;
            }
        }
        array_push($tokens, new Token(TT::EOF->value, $this->pos));
        return $tokens;
    }

    private function makeNumber(): Token
    {
        $numString = '';
        $dotCount = 0;
        $posStart = $this->pos->copy();

        while ($this->currentChar != null && str_contains(DIGITS . '.', $this->currentChar)) {

            if($this->currentChar === '.') {
                if ($dotCount == 1) {
                    break;
                }
                $dotCount++;
                $numString = $numString . '.';
            } else {
                $numString = $numString . $this->currentChar;
            }
            $this->advance();

        }
        if($dotCount === 0) {
            return new Token(TT::INT->value, $posStart, $this->pos, (int) $numString);
        } else {
            return new Token(TT::FLOAT->value, $posStart, $this->pos, (float) $numString);
        }
    }
}
