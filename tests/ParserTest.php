<?php

declare(strict_types=1);

use Bachalang\Lexer;
use Bachalang\Parser;
use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    public function testCanParseAtoms(): void
    {
        // Test text for the lexer to read
        $text = '6';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseSumAndSub(): void
    {
        // Test text for the lexer to read
        $text = '3 + 5 - 2';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseMulAndDiv(): void
    {
        // Test text for the lexer to read
        $text = '3 * 5 / 15';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParsePower(): void
    {
        // Test text for the lexer to read
        $text = '6^3';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseArithExpressions(): void
    {
        // Test text for the lexer to read
        $text = '((3 + 5) - 4 * (5 - 3) ^ 2 / 1) * 3 - 2 + (3 + 7)';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseEquality(): void
    {
        // Test text for the lexer to read
        $text = '3 == 5';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseNotEquality(): void
    {
        // Test text for the lexer to read
        $text = '3 != 5';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseLessThan(): void
    {
        // Test text for the lexer to read
        $text = '3 < 5';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseLessOrEqualThan(): void
    {
        // Test text for the lexer to read
        $text = '3 <= 5';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseGreaterThan(): void
    {
        // Test text for the lexer to read
        $text = '3 > 5';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseGreaterOrEqualThan(): void
    {
        // Test text for the lexer to read
        $text = '3 >= 5';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseNegation(): void
    {
        // Test text for the lexer to read
        $text = '!true';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseAndComp(): void
    {
        // Test text for the lexer to read
        $text = '3 == 5 && 3 < 5';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseOrComp(): void
    {
        // Test text for the lexer to read
        $text = '3 == 5 || 3 < 6';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseCompExpressions(): void
    {
        // Test text for the lexer to read
        $text = '!((1 - 2) < 3 && 4 <= 5 || 6 > 7 && 8 >= 9 || 1 == 2 && 1 != 2)';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseArrayExpr(): void
    {
        // Test text for the lexer to read
        $text = '[1,"string",3.14]';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseIfExpr(): void
    {
        // Test text for the lexer to read
        $text = 'if true { "expr" } elseif false { 1/1 } else { 3 / 3 } ';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseForExpr(): void
    {
        // Test text for the lexer to read
        $text = 'for i = 1 to 10 step 2 { "hola" }';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseWhileExpr(): void
    {
        // Test text for the lexer to read
        $text = 'while numero < 3 { let numero = numero + 1 }';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseFuncDef(): void
    {
        // Test text for the lexer to read
        $text = 'function sum(a,b) => a + b';

        // Making lexer with the test text
        $lexer = new Lexer('<stdin>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        // Read tokens and make AST with them
        $parser = new Parser($tokens);
        $ast = $parser->run();

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }

    public function testCanParseCalls(): void
    {
        $texts = [
            'function sum(a,b) => a + b',
            'sum(3,2)'
        ];
        foreach ($texts as $text) {
            // Making lexer with the test text
            $lexer = new Lexer('<stdin>', $text);

            // Making tokens and checking for errors
            $tokens = $lexer->makeTokens();

            // Read tokens and make AST with them
            $parser = new Parser($tokens);
            $ast = $parser->run();
        }

        // If there was no errors, everything works fine
        $this->assertNull($ast->error);
    }
}
