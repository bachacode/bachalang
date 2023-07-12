<?php

declare(strict_types=1);

define('DIGITS', '0123456789');

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

use Bachalang\Lexer;
use Bachalang\Parser;

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
            echo $ast->node . PHP_EOL;
        }
    }
}
