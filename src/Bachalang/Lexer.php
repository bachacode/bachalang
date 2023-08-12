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

    /**
     * @var Token[]
     */
    public array $tokens = [];

    public function __construct(
        private string $fn,
        private string $text,
        private ?string $ch = null,
        public ?string $error = null
    ) {
        $this->pos = new Position(-1, 0, -1, $fn, $this->text);
        $this->advance();
    }

    private function advance(): void
    {
        $this->pos->advance($this->ch);
        if($this->pos->index < strlen($this->text)) {
            $this->ch = $this->text[$this->pos->index];
        } else {
            $this->ch = null;
        }
    }

    /**
     * @return Token[]
     */
    public function &makeTokens(): array
    {
        while ($this->ch != null && $this->error == null) {
            if(ctype_space($this->ch)) {
                $this->advance();
            } elseif (str_contains(LETTERS, $this->ch)) {
                $this->tokens[] = $this->makeIdentifier();
            } elseif ($this->ch == '"') {
                $this->tokens[] = $this->makeString();
            } elseif (TokenType::checkToken(($this->ch))) {
                $this->tokens[] = $this->makeDelOrOps();
            } elseif(str_contains(DIGITS, $this->ch)) {
                $this->tokens[] = $this->makeNumber();
            } elseif($this->ch == '=') {
                $this->tokens[] = $this->makeEquals();
            } elseif($this->ch == '!') {
                $this->tokens[] = $this->makeNotEqualOrNot();
            } elseif($this->ch == '<') {
                $this->tokens = $this->makeLessThan();
            } elseif($this->ch == '>') {
                $this->tokens = $this->makeGreaterThan();
            } elseif($this->ch == '&' || $this->ch == '|') {
                $this->tokens = $this->makeCompExpr();
            } else {
                $posStart = clone $this->pos;
                $char = $this->ch;
                $this->advance();
                $this->error = "ERROR: ".(string) new IllegalCharError(
                    $posStart,
                    $this->pos,
                    "the following character is not permited >> {$char}"
                ) . PHP_EOL;
            }
        }
        $this->tokens[] = new Token(TokenType::EOF, $this->pos);
        return $this->tokens;
    }

    private function makeDelOrOps()
    {
        $char = $this->ch;
        $posStart = clone $this->pos;
        $this->advance();
        return new Token(TokenType::getTokenType($char), $posStart);
    }

    private function makeCompExpr(): Token
    {
        $keywordValue = $this->ch;
        $posStart = clone $this->pos;

        $this->advance();

        if($this->ch == null || !str_contains($this->ch, $keywordValue)) {
            $this->error = "ERROR: ".(string) new ExpectedCharError(
                $posStart,
                $this->pos,
                "The following character is not permited >> '{$this->ch}' after '{$keywordValue}'"
            ) . PHP_EOL;
        }
        $keywordValue .= $this->ch;
        $this->advance();
        return new Token(TokenType::KEYWORD, $posStart, $this->pos, $keywordValue);

    }

    private function makeIdentifier(): Token
    {
        $idStr = '';
        $posStart = clone $this->pos;

        while ($this->ch != null && str_contains(LETTERS_DIGITS . '_', $this->ch)) {
            $idStr .= $this->ch;
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
        $posStart = clone $this->pos;
        $escapeChar = false;
        $this->advance();

        $escapeCharacters =  [
            'n' => '\n',
            't' => '\t'
        ];

        while ($this->ch != null && $this->ch != '"' || $escapeChar == true) {
            if($escapeChar == true) {
                $string .= $escapeCharacters[$this->ch] ?? $this->ch;
                $escapeChar = false;
            } else {
                if($this->ch == '\\') {
                    $escapeChar = true;
                } else {
                    $string .= $this->ch;
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
        $posStart = clone $this->pos;
        $this->advance();
        if($this->ch != null && str_contains('=', $this->ch)) {
            $this->advance();
            return new Token($firstType, $posStart, $this->pos);
        } elseif(!is_null($thirdType) && str_contains('>', $this->ch)) {
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
        $posStart = clone $this->pos;

        while ($this->ch != null && str_contains(DIGITS . '.', $this->ch)) {

            if($this->ch === '.') {
                if ($dotCount == 1) {
                    break;
                }
                $dotCount++;
                $numString .= '.';
            } else {
                $numString .= $this->ch;
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
