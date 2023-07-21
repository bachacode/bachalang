<?php

declare(strict_types=1);

define('DIGITS', '0123456789');
define('LETTERS', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
define('LETTERS_DIGITS', DIGITS . LETTERS);
define('KEYWORDS', ['var']);

spl_autoload_register(function ($class) {
    // replace namespace separators with directory separators in the relative
    // class name, append with .php
    $class_path = str_replace('\\', '/', $class);

    $file =  __DIR__ . '/src/' . $class_path . '.php';
    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

use Bachalang\Context;
use Bachalang\Interpreter;
use Bachalang\Lexer;
use Bachalang\Parser;
use Bachalang\SymbolTable;

$globalSymbolTable = new SymbolTable();
$globalSymbolTable->set('null', 0);

while (true) {
    $text = readline('bachalang > ');
    $lexer = new Lexer('<stdin>', $text);
    $tokens = $lexer->makeTokens();
    if(is_string($tokens)) {
        echo $tokens;
    } else {
        $parser = new Parser($tokens);
        $ast = $parser->run();
        if($ast->error != null) {
            echo $ast->error . PHP_EOL;
        } else {
            $interpreter = new Interpreter();
            $context = new Context(
                displayName: '<program>',
                symbolTable: $globalSymbolTable
            );
            // var_dump($context);
            $result = $interpreter->visit($ast->node, $context);
            if($result->error != null) {
                echo $result->error . PHP_EOL;
            } else {
                echo $result->value . PHP_EOL;
            }
        }
    }
}
