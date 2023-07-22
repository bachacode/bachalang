<?php

declare(strict_types=1);

namespace Bachalang;

class Runner
{
    private Lexer $lexer;
    private Parser $parser;
    private Interpreter $interpreter;
    private SymbolTable $globalSymbolTable;
    private Context $context;

    public function __construct()
    {
        // Create lexer - Used to convert plain text into tokens
        $this->lexer = new Lexer('<stdin>');

        // Create Global Symbol Table - Keep track of variables
        $this->globalSymbolTable = new SymbolTable();
        $this->globalSymbolTable->set('null', 0);

        // Create Parser - Used to convert tokens into an Abstract Syntax Tree
        $this->parser = new Parser();

        // Interpreter - used to translate the Abstract Syntax Tree into human readable behaviour
        $this->interpreter = new Interpreter();

        // Context - In with context is the current code executing
        $this->context = new Context(
            displayName: '<program>',
            symbolTable: $this->globalSymbolTable
        );
    }

    public function run(string $text)
    {
        // Read text and make tokens with it
        $this->lexer->setText($text);
        $tokens = $this->lexer->makeTokens();

        // Check for InvalidSyntaxErrors
        if($this->lexer->error != null) {
            return $this->lexer->error;
        }

        // Read tokens and make AST with them
        $this->parser->setTokens($tokens);
        $ast = $this->parser->run();

        // Check for InvalidSyntaxErrors
        if($ast->error != null) {
            return $ast->error;
        }

        // Visit every node of the AST and return a Runtime Result;
        $runtime = $this->interpreter->visit($ast->node, $this->context);

        // Check for RuntimeErrors
        if($runtime->error != null) {
            return $runtime->error;
        }

        // If not errors detected, return the Runtime Result;
        return $runtime->result;
    }
}
