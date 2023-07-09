<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Token;

use Bachalang\TT;

class Lexer
{
    public Position $pos;

    public function __construct(
        public string $fn,
        public string $text,
        public ?string $currentChar = null
    ) {
        $this->pos = new Position(-1, 0, -1, $fn, $this->text);
        $this->advance();
    }

    public function advance()
    {
        $this->pos->advance($this->currentChar);
        if($this->pos->index < strlen($this->text)) {
            $this->currentChar = $this->text[$this->pos->index];
        } else {
            $this->currentChar = null;
        }
    }

    public function makeTokens()
    {
        $tokens = [];

        while ($this->currentChar != null) {
            if(ctype_space($this->currentChar)) {
                $this->advance();
            } elseif (TT::checkToken(($this->currentChar))) {
                array_push($tokens, (string) new Token(TT::getToken($this->currentChar)));
                $this->advance();
            } elseif(str_contains(DIGITS, $this->currentChar)) {
                array_push($tokens, $this->makeNumber());
            } else {
                $posStart = $this->pos->copy();
                $char = $this->currentChar;
                $this->advance();
                return "ERROR: " . (string) (new IllegalCharError($posStart, $this->pos, 'the following character is not permited: ' . "-> " . $char . " <-")) . PHP_EOL;
            }
        }
        return $tokens;
    }

    public function makeNumber()
    {
        $numString = '';
        $dotCount = 0;

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
            return (string) new Token(TT::INT->value, (int) $numString);
        } else {
            return (string) new Token(TT::FLOAT->value, (float) $numString);
        }
    }
}
