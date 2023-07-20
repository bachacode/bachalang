<?php

declare(strict_types=1);

namespace Bachalang;

enum TT: string
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
    case EQUALS = 'EQUALS';
    case LPAREN = 'LPAREN';
    case RPAREN = 'RPAREN';
    case EOF = 'EOF';

    public static function getToken(string $char): string
    {
        return match($char) {
            '+' => TT::PLUS->value,
            '-' => TT::MINUS->value,
            '*' => TT::MUL->value,
            '/' => TT::DIV->value,
            '^' => TT::POW->value,
            '=' => TT::EQUALS->value,
            '(' => TT::LPAREN->value,
            ')' => TT::RPAREN->value,
        };
    }

    public static function checkToken(mixed $char): bool
    {
        return match($char) {
            '+', '-', '*', '/', '^', '=', '(', ')'  => true,
            default => false
        };
    }

}
