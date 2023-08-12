<?php

declare(strict_types=1);

namespace Bachalang;

enum TokenType: string
{
    case ILLEGAL = 'ILLEGAL';
    case EOF = 'EOF';

    // Identifiers + literals
    case IDENTIFIER = 'IDENTIFIER'; // add, foobar, x, y, ...
    case INT = 'INT'; // 1343456
    case FLOAT = 'FLOAT'; // 3.14
    case STRING = 'STRING'; // "Hello World"

    // Delimiters
    case COMMA = 'COMMA';
    case SEMICOLON = 'SEMICOLON';

    case LPAREN = 'LPAREN';
    case RPAREN = 'RPAREN';
    case LSQUARE = 'LSQUARE';
    case RSQUARE = 'RSQUARE';
    case LCURLY = 'LCURLY';
    case RCURLY = 'RCURLY';
    case DQUOTES = 'DQUOTES';

    // Operators
    case PLUS = 'PLUS';
    case MINUS = 'MINUS';
    case MUL = 'MUL';
    case DIV = 'DIV';
    case POW = 'POW';
    case EQUALS = 'EQUALS';

    // Keywords
    case FUNCTION = "FUNCTION";
    case LET = "LET";
    case KEYWORD = 'KEYWORD';
    case ARROW = 'ARROW';

    // Comparators
    case EE = 'EE';
    case NOT = 'NOT';
    case NE = 'NE';
    case LT = 'LT';
    case GT = 'GT';
    case LTE = 'LTE';
    case GTE = 'GTE';

    public static function getTokenType(string $char): TokenType
    {
        return match($char) {
            '+' => TokenType::PLUS,
            '-' => TokenType::MINUS,
            '*' => TokenType::MUL,
            '/' => TokenType::DIV,
            '^' => TokenType::POW,
            '(' => TokenType::LPAREN,
            ')' => TokenType::RPAREN,
            '[' => TokenType::LSQUARE,
            ']' => TokenType::RSQUARE,
            '{' => TokenType::LCURLY,
            '}' => TokenType::RCURLY,
            ',' => TokenType::COMMA,
        };
    }

    public static function checkToken(mixed $char): bool
    {
        return match($char) {
            '+', '-', '*', '/', '^', '(', ')', '[', ']', '{', '}', ','  => true,
            default => false
        };
    }

    public function checkOperator(): bool
    {
        return match($this) {
            TokenType::PLUS, TokenType::MINUS, TokenType::MUL,
            TokenType::DIV, TokenType::POW, TokenType::EE,
            TokenType::NE, TokenType::LT, TokenType::GT,
            TokenType::LTE, TokenType::GTE => true,
            default => false
        };
    }

    public function getOperator(): string
    {
        return match($this) {
            TokenType::PLUS => 'addedTo',
            TokenType::MINUS => 'substractedBy',
            TokenType::MUL => 'multipliedBy',
            TokenType::DIV => 'dividedBy',
            TokenType::POW => 'powBy',
            TokenType::EE => 'getComparisonEq',
            TokenType::NE => 'getComparisonNe',
            TokenType::LT => 'getComparisonLt',
            TokenType::GT => 'getComparisonGt',
            TokenType::LTE => 'getComparisonLte',
            TokenType::GTE => 'getComparisonGte'
        };
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

}
