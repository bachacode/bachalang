<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Errors\ExpectedCharError;
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
        public ?string $error = null
    ) {
        $this->pos = new Position(-1, 0, -1, $fn, $this->text);
        $this->advance();
    }

    public function setText(string $text): void
    {
        $this->pos = new Position(-1, 0, -1, $this->fn, $this->text);
        $this->text = $text;
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

    public function makeTokens(): array
    {
        $tokens = [];

        while ($this->currentChar != null && $this->error == null) {
            if(ctype_space($this->currentChar)) {
                $this->advance();
            } elseif (str_contains(LETTERS, $this->currentChar)) {
                array_push($tokens, $this->makeIdentifier());
                // $this->advance();
            } elseif ($this->currentChar == '"') {
                array_push($tokens, $this->makeString());
                // $this->advance();
            } elseif (TokenType::checkToken(($this->currentChar))) {
                array_push($tokens, new Token(TokenType::getToken($this->currentChar), $this->pos));
                $this->advance();
            } elseif(str_contains(DIGITS, $this->currentChar)) {
                array_push($tokens, $this->makeNumber());
            } elseif($this->currentChar == '=') {
                array_push($tokens, $this->makeEquals());
            } elseif($this->currentChar == '!') {
                array_push($tokens, $this->makeNotEqualOrNot());
            } elseif($this->currentChar == '<') {
                array_push($tokens, $this->makeLessThan());
            } elseif($this->currentChar == '>') {
                array_push($tokens, $this->makeGreaterThan());
            } elseif($this->currentChar == '&' || $this->currentChar == '|') {
                array_push($tokens, $this->makeCompExprToken());
            } else {
                $posStart = $this->pos->copy();
                $char = $this->currentChar;
                $this->advance();
                $this->error = "ERROR: ".(string) new IllegalCharError(
                    $posStart,
                    $this->pos,
                    "the following character is not permited >> {$char}"
                ) . PHP_EOL;
            }
        }
        array_push($tokens, new Token(TokenType::EOF, $this->pos));
        return $tokens;
    }

    private function makeCompExprToken(): Token
    {
        $keywordValue = $this->currentChar;
        $posStart = $this->pos->copy();

        $this->advance();

        if($this->currentChar == null || !str_contains($this->currentChar, $keywordValue)) {
            $this->error = "ERROR: ".(string) new ExpectedCharError(
                $posStart,
                $this->pos,
                "The following character is not permited >> '{$this->currentChar}' after '{$keywordValue}'"
            ) . PHP_EOL;
        }
        $keywordValue .= $this->currentChar;
        $this->advance();
        return new Token(TokenType::KEYWORD, $posStart, $this->pos, $keywordValue);

    }

    private function makeIdentifier(): Token
    {
        $idStr = '';
        $posStart = $this->pos->copy();

        while ($this->currentChar != null && str_contains(LETTERS_DIGITS . '_', $this->currentChar)) {
            $idStr .= $this->currentChar;
            $this->advance();
        }

        if(in_array($idStr, KEYWORDS)) {
            $tokenType = TokenType::KEYWORD;
        } else {
            $tokenType = TokenType::IDENTIFIER;
        }

        return new Token($tokenType, $posStart, $this->pos, $idStr);
    }

    public function makeString(): Token
    {
        $string = '';
        $posStart = $this->pos->copy();
        $escapeChar = false;
        $this->advance();

        $escapeCharacters =  [
            'n' => '\n',
            't' => '\t'
        ];

        while ($this->currentChar != null && $this->currentChar != '"' || $escapeChar == true) {
            if($escapeChar == true) {
                $string .= $escapeCharacters[$this->currentChar] ?? $this->currentChar;
                $escapeChar = false;
            } else {
                if($this->currentChar == '\\') {
                    $escapeChar = true;
                } else {
                    $string .= $this->currentChar;
                }
            }
            $this->advance();
        }
        $this->advance();
        return new Token(TokenType::STRING, $posStart, $this->pos, $string);

    }

    private function makeEquals(): Token
    {
        return $this->getTokenType(TokenType::EE, TokenType::EQUALS, TokenType::ARROW);
    }

    private function makeLessThan(): Token
    {
        return $this->getTokenType(TokenType::LTE, TokenType::LT);
    }

    private function makeGreaterThan(): Token
    {
        return $this->getTokenType(TokenType::GTE, TokenType::GT);
    }

    private function makeNotEqualOrNot(): Token
    {
        return $this->getTokenType(TokenType::NE, TokenType::NOT);
    }

    private function getTokenType(TokenType $firstType, TokenType $secondType, ?TokenType $thirdType = null): Token
    {
        $posStart = $this->pos->copy();
        $this->advance();
        if($this->currentChar != null && str_contains('=', $this->currentChar)) {
            $this->advance();
            return new Token($firstType, $posStart, $this->pos);
        } elseif(!is_null($thirdType) && str_contains('>', $this->currentChar)) {
            $this->advance();
            return new Token($thirdType, $posStart, $this->pos);
        } else {
            return new Token($secondType, $posStart, $this->pos);
        }
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
                $numString .= '.';
            } else {
                $numString .= $this->currentChar;
            }
            $this->advance();

        }
        if($dotCount === 0) {
            return new Token(TokenType::INT, $posStart, $this->pos, (int) $numString);
        } else {
            return new Token(TokenType::FLOAT, $posStart, $this->pos, (float) $numString);
        }
    }
}
