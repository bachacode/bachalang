<?php

declare(strict_types=1);

use Bachalang\Context;
use Bachalang\Interpreter;
use Bachalang\Lexer;
use Bachalang\Parser;
use Bachalang\SymbolTable;
use Bachalang\Values\ArrayVal;
use Bachalang\Values\Number;
use PHPUnit\Framework\TestCase;

final class InterpreterTest extends TestCase
{
    public function testCanVisitNumberNode(): void
    {
        // Test text for the lexer to read
        $text = '6';
        $expected = 6;
        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->parse();

        $globalSymbolTable = new SymbolTable();
        $context = new Context(
            displayName: '<program>',
            symbolTable: $globalSymbolTable
        );

        // Visit every node of the AST and return a Runtime Result;
        $runtime = Interpreter::visit($ast->node, $context);

        // If there was no errors, everything works fine
        $this->assertNull($runtime->error);

        // Asserts that the result is the expected
        if($runtime->result instanceof ArrayVal) {
            $this->assertEquals($expected, $runtime->result->elements[0]->value);
        } else {
            $this->assertEquals($expected, $runtime->result->value);
        }
    }

    public function testCanVisitStringNode(): void
    {
        // Test text for the lexer to read
        $text = '"Hello World"';
        $expected = "Hello World";

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->parse();

        $globalSymbolTable = new SymbolTable();
        $context = new Context(
            displayName: '<program>',
            symbolTable: $globalSymbolTable
        );

        // Visit every node of the AST and return a Runtime Result;
        $runtime = Interpreter::visit($ast->node, $context);

        // If there was no errors, everything works fine
        $this->assertNull($runtime->error);

        // Asserts that the result is the expected
        if($runtime->result instanceof ArrayVal) {
            $this->assertEquals($expected, $runtime->result->elements[0]);
        } else {
            $this->assertEquals($expected, $runtime->result->value);
        }
    }

    public function testCanVisitArrayNode(): void
    {
        // Test text for the lexer to read
        $text = '[1,2,3]';
        $expected = '[1,2,3]';
        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->parse();

        $globalSymbolTable = new SymbolTable();
        $context = new Context(
            displayName: '<program>',
            symbolTable: $globalSymbolTable
        );

        // Visit every node of the AST and return a Runtime Result;
        $runtime = Interpreter::visit($ast->node, $context);

        // If there was no errors, everything works fine
        $this->assertNull($runtime->error);

        // Asserts that the result is the expected
        if($runtime->result instanceof ArrayVal) {
            $this->assertEquals($expected, $runtime->result->elements[0]);
        } else {
            $this->assertEquals($expected, $runtime->result->value);
        }
    }

    public function testCanVisitVarAssignNodeAndVarAccessNode(): void
    {
        $texts = [
            'let numero = 5',
            'numero'
        ];
        $expected = 5;
        $globalSymbolTable = new SymbolTable();
        $context = new Context(
            displayName: '<program>',
            symbolTable: $globalSymbolTable
        );

        foreach ($texts as $text) {
            // Making lexer with the test text
            $lexer = new Lexer('<stdin>', $text);

            // Making tokens and checking for errors
            $tokens = $lexer->makeTokens();

            // Read tokens and make AST with them
            $parser = new Parser($tokens);
            $ast = $parser->parse();

            // Visit every node of the AST and return a Runtime Result;
            $runtime = Interpreter::visit($ast->node, $context);

            // If there was no errors, everything works fine
            $this->assertNull($runtime->error);
        }
        // Asserts that the result is the expected
        if($runtime->result instanceof ArrayVal) {
            $this->assertEquals($expected, $runtime->result->elements[0]->value);
        } else {
            $this->assertEquals($expected, $runtime->result->value);
        }
    }

    public function testCanVisitBinOpNode(): void
    {
        // Test text for the lexer to read
        $text = '3 + 3';
        $expected = 6;
        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->parse();

        $globalSymbolTable = new SymbolTable();
        $context = new Context(
            displayName: '<program>',
            symbolTable: $globalSymbolTable
        );

        // Visit every node of the AST and return a Runtime Result;
        $runtime = Interpreter::visit($ast->node, $context);

        // If there was no errors, everything works fine
        $this->assertNull($runtime->error);

        // Asserts that the result is the expected
        if($runtime->result instanceof ArrayVal) {
            $this->assertEquals($expected, $runtime->result->elements[0]->value);
        } else {
            $this->assertEquals($expected, $runtime->result->value);
        }
    }

    public function testCanVisitUnaryOpNode(): void
    {
        // Test text for the lexer to read
        $text = '-5';
        $expected = -5;
        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->parse();

        $globalSymbolTable = new SymbolTable();
        $context = new Context(
            displayName: '<program>',
            symbolTable: $globalSymbolTable
        );

        // Visit every node of the AST and return a Runtime Result;
        $runtime = Interpreter::visit($ast->node, $context);

        // If there was no errors, everything works fine
        $this->assertNull($runtime->error);

        // Asserts that the result is the expected
        if($runtime->result instanceof ArrayVal) {
            $this->assertEquals($expected, $runtime->result->elements[0]->value);
        } else {
            $this->assertEquals($expected, $runtime->result->value);
        }
    }

    public function testCanVisitIfNode(): void
    {
        // Test text for the lexer to read
        $text = 'if 1 < 1 { "false" } elseif 1 < 5 { "true" } else { "else" }';
        $expected = "true";
        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->parse();

        $globalSymbolTable = new SymbolTable();
        $context = new Context(
            displayName: '<program>',
            symbolTable: $globalSymbolTable
        );

        // Visit every node of the AST and return a Runtime Result;
        $runtime = Interpreter::visit($ast->node, $context);

        // If there was no errors, everything works fine
        $this->assertNull($runtime->error);

        // Asserts that the result is the expected
        if($runtime->result instanceof ArrayVal) {
            $this->assertEquals($expected, $runtime->result->elements[0]->elements[0]->value);
        } else {
            $this->assertEquals($expected, $runtime->result->value);
        }
    }

    public function testCanVisitForNode(): void
    {
        // Test text for the lexer to read
        $text = 'for i = 1 to 10 step 2 { "hola" }';
        $expected = "[hola,hola,hola,hola,hola]";
        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->parse();

        $globalSymbolTable = new SymbolTable();
        $context = new Context(
            displayName: '<program>',
            symbolTable: $globalSymbolTable
        );

        // Visit every node of the AST and return a Runtime Result;
        $runtime = Interpreter::visit($ast->node, $context);

        // If there was no errors, everything works fine
        $this->assertNull($runtime->error);

        // Asserts that the result is the expected
        if($runtime->result instanceof ArrayVal) {
            $this->assertEquals($expected, $runtime->result->elements[0]);
        } else {
            $this->assertEquals($expected, $runtime->result->value);
        }
    }

    public function testCanVisitWhileNode(): void
    {
        $texts = [
            'let numero = 1',
            'while numero < 3 { let numero = numero + 1 }'
        ];
        $expected = "[2,3]";
        $globalSymbolTable = new SymbolTable();
        $context = new Context(
            displayName: '<program>',
            symbolTable: $globalSymbolTable
        );

        foreach ($texts as $text) {
            // Making lexer with the test text
            $lexer = new Lexer('<stdin>', $text);

            // Making tokens and checking for errors
            $tokens = $lexer->makeTokens();

            // Read tokens and make AST with them
            $parser = new Parser($tokens);
            $ast = $parser->parse();

            // Visit every node of the AST and return a Runtime Result;
            $runtime = Interpreter::visit($ast->node, $context);

            // If there was no errors, everything works fine
            $this->assertNull($runtime->error);
        }

        // If there was no errors, everything works fine
        $this->assertNull($runtime->error);

        // Asserts that the result is the expected
        if($runtime->result instanceof ArrayVal) {
            $this->assertEquals($expected, $runtime->result->elements[0]);
        } else {
            $this->assertEquals($expected, $runtime->result->value);
        }
    }

    public function testCanVisitFuncDefAndCallNodes(): void
    {
        $texts = [
            'function sum(a,b) => a + b',
            'sum(3,2)'
        ];
        $expected = 5;
        $globalSymbolTable = new SymbolTable();
        $context = new Context(
            displayName: '<program>',
            symbolTable: $globalSymbolTable
        );

        foreach ($texts as $text) {
            // Making lexer with the test text
            $lexer = new Lexer('<stdin>', $text);

            // Making tokens and checking for errors
            $tokens = $lexer->makeTokens();

            // Read tokens and make AST with them
            $parser = new Parser($tokens);
            $ast = $parser->parse();

            // Visit every node of the AST and return a Runtime Result;
            $runtime = Interpreter::visit($ast->node, $context);

            // If there was no errors, everything works fine
            $this->assertNull($runtime->error);
        }

        // Asserts that the result is the expected
        if($runtime->result instanceof ArrayVal) {
            $this->assertEquals($expected, $runtime->result->elements[0]->value);
        } else {
            $this->assertEquals($expected, $runtime->result->value);
        }
    }
}
