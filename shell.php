<?php

declare(strict_types=1);

require_once('./contants.php');
require_once('./autoloader.php');

use Bachalang\Context;
use Bachalang\Interpreter;
use Bachalang\Lexer;
use Bachalang\Parser;
use Bachalang\SymbolTable;

function run($text)
{
    // Lexer - create tokens
    $lexer = new Lexer('<stdin>', $text);
    $tokens = $lexer->makeTokens();
    if($lexer->error != null) {
        return $lexer->error;
    }

    // Parser - Convert tokens given into a Abstract Syntax Tree
    $parser = new Parser($tokens);
    $ast = $parser->run();
    if($ast->error != null) {
        return $ast->error;
    }

    // Interpreter - Translate the Abstract Syntax Tree into human readabale behaviour
    $interpreter = new Interpreter();
    $globalSymbolTable = new SymbolTable();
    $globalSymbolTable->set('null', 0);
    $context = new Context(
        displayName: '<program>',
        symbolTable: $globalSymbolTable
    );
    $result = $interpreter->visit($ast->node, $context);
    if($result->error != null) {
        return $result->error;
    }
    return $result->value;
}
while (true) {
    $text = readline('bachalang > ');
    $result = run($text);
    echo $result . PHP_EOL;
}
