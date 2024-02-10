<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Tests\Functional;

use Phplrt\Lexer\Lexer;
use Phplrt\Lexer\Token\EndOfInput;
use Phplrt\Lexer\Token\Token;
use Phplrt\Source\Source;
use PHPUnit\Framework\Attributes\Group;

#[Group('phplrt/lexer'), Group('functional')]
class SimpleLexerTest extends TestCase
{
    public function testDigits(): void
    {
        $expected = $this->tokensOf([
            new Token('T_DIGIT', '23', 0),
            new Token('T_DIGIT', '42', 3),
            new EndOfInput(5),
        ]);

        $lexer = new Lexer(['T_WHITESPACE' => '\s+', 'T_DIGIT' => '\d+'], ['T_WHITESPACE']);

        $this->assertEquals($expected, $this->tokensOf(
            $lexer->lex(new Source('23 42')),
        ));
    }
}
