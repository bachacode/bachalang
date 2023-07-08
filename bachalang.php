<?php

declare(strict_types=1);

##########################################################
# CONSTANTS
##########################################################

define('DIGITS', '0123456789');

##########################################################
# Errors
##########################################################

class IllegalCharError extends Error
{
    protected $message = 'Illegal Character';
}

##########################################################
# Tokens
##########################################################

enum TT: string
{
    case INT = 'INT';
    case FLOAT = 'FLOAT';
    case PLUS = 'PLUS';
    case MINUS = 'MINUS';
    case MUL = 'MUL';
    case DIV = 'DIV';
    case LPAREN = 'LPAREN';
    case RPAREN = 'RPAREN';

    public static function getToken(string $char): string
    {
        return match($char) {
            '+' => TT::PLUS->value,
            '-' => TT::MINUS->value,
            '*' => TT::MUL->value,
            '/' => TT::DIV->value,
            '(' => TT::LPAREN->value,
            ')' => TT::RPAREN->value,

        };
    }
}

class Token
{
    public function __construct(
        public string $type,
        public $value = null
    ) {

    }

    public function __toString()
    {
        if($this->value != null) {
            return "$this->type:$this->value";
        }
        return "$this->type";
    }
}

##########################################################
# Lexer
##########################################################

class Lexer
{
    public function __construct(
        public string $text,
        public int $pos = -1,
        public $currentChar = null
    ) {
        $this->advance();
    }

    public function advance()
    {
        $this->pos++;
        if($this->pos < strlen($this->text)) {
            $this->currentChar = $this->text[$this->pos];
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
            } elseif (!str_contains(DIGITS . '.', $this->currentChar)) {
                array_push($tokens, (string) new Token(TT::getToken($this->currentChar)));
                $this->advance();
            } elseif(str_contains(DIGITS, $this->currentChar)) {
                array_push($tokens, $this->makeNumber());
            } else {
                $char = $this->currentChar;
                $this->advance();
                throw new IllegalCharError();
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

##########################################################
# Run
##########################################################

function run($text)
{
    $lexer = new Lexer($text);
    $tokens = $lexer->makeTokens();

    return $tokens;
}
