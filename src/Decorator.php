<?php

declare(strict_types=1);

namespace Phplrt\Lexer;

use Phplrt\Contracts\Lexer\LexerInterface;
use Phplrt\Contracts\Source\ReadableInterface;

abstract class Decorator implements LexerInterface
{
    public function __construct(
        private readonly LexerInterface $lexer,
    ) {}

    public function lex(ReadableInterface $source, int $offset = 0, int $length = null): iterable
    {
        return $this->lexer->lex($source, $offset, $length);
    }
}
