<?php

declare(strict_types=1);

namespace Bachalang;

enum TokenType: string
{
    case INT = 'INT';
    case FLOAT = 'FLOAT';
    case IDENTIFIER = 'IDENTIFIER';
    case KEYWORD = 'KEYWORD';
    case PLUS = 'PLUS';
    case MINUS = 'MINUS';
    case MUL = 'MUL';
    case DIV = 'DIV';
    case POW = 'POW';
    case LPAREN = 'LPAREN';
    case RPAREN = 'RPAREN';
    case EQUALS = 'EQUALS';
    case EE = 'EE';
    case NOT = 'NOT';
    case NE = 'NE';
    case LT = 'LT';
    case GT = 'GT';
    case LTE = 'LTE';
    case GTE = 'GTE';
    case EOF = 'EOF';

    public static function getToken(string $char): TokenType
    {
        return match($char) {
            '+' => TokenType::PLUS,
            '-' => TokenType::MINUS,
            '*' => TokenType::MUL,
            '/' => TokenType::DIV,
            '^' => TokenType::POW,
            '(' => TokenType::LPAREN,
            ')' => TokenType::RPAREN,
        };
    }

    public static function checkToken(mixed $char): bool
    {
        return match($char) {
            '+', '-', '*', '/', '^', '(', ')'  => true,
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

}
