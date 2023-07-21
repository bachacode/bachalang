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

}
