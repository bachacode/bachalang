<?php

declare(strict_types=1);

use Bachalang\Lexer;
use Bachalang\TokenType;
use PHPUnit\Framework\TestCase;

final class LexerTest extends TestCase
{
    protected $backupGlobals = true;

    public function testCanCreateIdentifier(): void
    {
        // Test text for the lexer to read
        $text = 'add foobar x y';

        // Making lexer with the test text
        $lexer = new Lexer('<identifiers>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();
        foreach ($tokens as $token) {
            $this->assertTrue(TokenType::IDENTIFIER == $token->type ||TokenType::EOF == $token->type);
        }
    }

    public function testCanCreateLiterals(): void
    {
        // Test text for the lexer to read
        $text = '"hola mundo" 5 3.14';

        // Making lexer with the test text
        $lexer = new Lexer('<literals>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        foreach ($tokens as $key => $token) {
            switch ($key) {
                case 0:
                    $this->assertTrue(TokenType::STRING == $token->type);
                    break;

                case 1:
                    $this->assertTrue(TokenType::INT == $token->type);
                    break;

                case 2:
                    $this->assertTrue(TokenType::FLOAT == $token->type);
                    break;

                default:
                    $this->assertTrue(TokenType::EOF == $token->type);
                    break;
            }
        }
    }

    public function testCanCreateDelimiters(): void
    {
        // Test text for the lexer to read
        $text = '()[]{},';

        // Making lexer with the test text
        $lexer = new Lexer('<delimiters>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        foreach ($tokens as $key => $token) {
            switch ($key) {
                case 0:
                    $this->assertTrue(TokenType::LPAREN == $token->type);
                    break;

                case 1:
                    $this->assertTrue(TokenType::RPAREN == $token->type);
                    break;

                case 2:
                    $this->assertTrue(TokenType::LSQUARE == $token->type);
                    break;

                case 3:
                    $this->assertTrue(TokenType::RSQUARE == $token->type);
                    break;

                case 4:
                    $this->assertTrue(TokenType::LCURLY == $token->type);
                    break;

                case 5:
                    $this->assertTrue(TokenType::RCURLY == $token->type);
                    break;

                case 6:
                    $this->assertTrue(TokenType::COMMA == $token->type);
                    break;

                default:
                    $this->assertTrue(TokenType::EOF == $token->type);
                    break;
            }
        }
    }

    public function testCanCreateKeywords(): void
    {
        // Test text for the lexer to read
        $text = 'let && || if elseif else for to step while function';

        // Making lexer with the test text
        $lexer = new Lexer('<delimiters>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        foreach ($tokens as $key => $token) {
            switch ($key) {
                case 0:
                    $this->assertTrue($token->matches(TokenType::KEYWORD, 'let'));
                    break;

                case 1:
                    $this->assertTrue($token->matches(TokenType::KEYWORD, '&&'));
                    break;

                case 2:
                    $this->assertTrue($token->matches(TokenType::KEYWORD, '||'));
                    break;

                case 3:
                    $this->assertTrue($token->matches(TokenType::KEYWORD, 'if'));
                    break;

                case 4:
                    $this->assertTrue($token->matches(TokenType::KEYWORD, 'elseif'));
                    break;

                case 5:
                    $this->assertTrue($token->matches(TokenType::KEYWORD, 'else'));
                    break;

                case 6:
                    $this->assertTrue($token->matches(TokenType::KEYWORD, 'for'));
                    break;

                case 7:
                    $this->assertTrue($token->matches(TokenType::KEYWORD, 'to'));
                    break;

                case 8:
                    $this->assertTrue($token->matches(TokenType::KEYWORD, 'step'));
                    break;

                case 9:
                    $this->assertTrue($token->matches(TokenType::KEYWORD, 'while'));
                    break;

                case 10:
                    $this->assertTrue($token->matches(TokenType::KEYWORD, 'function'));
                    break;

                default:
                    $this->assertTrue(TokenType::EOF == $token->type);
                    break;
            }
        }
    }

    public function testCanCreateComparators(): void
    {
        // Test text for the lexer to read
        $text = '== ! != < <= > >=';

        // Making lexer with the test text
        $lexer = new Lexer('<delimiters>', $text);

        // Making tokens and checking for errors
        $tokens = $lexer->makeTokens();

        foreach ($tokens as $key => $token) {
            switch ($key) {
                case 0:
                    $this->assertTrue(TokenType::EE == $token->type);
                    break;

                case 1:
                    $this->assertTrue(TokenType::NOT == $token->type);
                    break;

                case 2:
                    $this->assertTrue(TokenType::NE == $token->type);
                    break;

                case 3:
                    $this->assertTrue(TokenType::LT == $token->type);
                    break;

                case 4:
                    $this->assertTrue(TokenType::LTE == $token->type);
                    break;

                case 5:
                    $this->assertTrue(TokenType::GT == $token->type);
                    break;

                case 6:
                    $this->assertTrue(TokenType::GTE == $token->type);
                    break;

                default:
                    $this->assertTrue(TokenType::EOF == $token->type);
                    break;
            }
        }
    }
}
