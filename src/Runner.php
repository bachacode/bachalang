<?php

declare(strict_types=1);

namespace Bachalang;

use Bachalang\Values\BuiltInFunc;
use Bachalang\Values\Number;
use Bachalang\Values\Value;

class Runner
{
    private Lexer $lexer;
    private Parser $parser;
    private SymbolTable $globalSymbolTable;
    private Context $context;

    public function __construct()
    {

        $builtInFunctions = [
            'print', 'print_return', 'input', 'input_int', 'clear',
            'is_number', 'is_string', 'is_array', 'is_function',
            'append', 'len', 'pop', 'extend', 'run'
        ];

        // Create Global Symbol Table - Keep track of variables
        $this->globalSymbolTable = new SymbolTable();
        $this->globalSymbolTable->set('null', Number::null());
        $this->globalSymbolTable->set('true', new Number(Number::TRUE));
        $this->globalSymbolTable->set('false', new Number(Number::FALSE));
        // Create Built-in functions
        foreach ($builtInFunctions as $funcName) {
            $this->globalSymbolTable->set($funcName, new BuiltInFunc($funcName));
        }
        // Create Parser - Used to convert tokens into an Abstract Syntax Tree

        // Context - In with context is the current code executing
        $this->context = new Context(
            displayName: '<program>',
            symbolTable: $this->globalSymbolTable
        );
    }

    public function run(string $fn, string $text): ?Value
    {
        // Read text and make tokens with it
        $this->lexer = new Lexer($fn, $text);
        // $this->lexer->setText($text);
        $tokens = $this->lexer->makeTokens();

        // Check for InvalidSyntaxErrors
        if(!is_null($this->lexer->error)) {
            $error = $this->lexer->error;
            $this->lexer->error = null;
            echo $error . PHP_EOL;
            return null;
        }
        // Read tokens and make AST with them
        $this->parser = new Parser($tokens);
        // $this->parser->setTokens($tokens);
        $ast = $this->parser->parse();

        // Check for InvalidSyntaxErrors
        if(!is_null($ast->error)) {
            echo (string) $ast->error  . PHP_EOL;
            return null;
        }
        // Visit every node of the AST and return a Runtime Result;
        $runtime = Interpreter::visit($ast->node, $this->context);

        // Check for RuntimeErrors
        if(!is_null($runtime->error)) {
            echo $runtime->error  . PHP_EOL;
            return null;
        }

        // If not errors detected, return the Runtime Result;
        return $runtime->result;
    }
}
