<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Aliases;

interface GeneratorInterface
{
    /**
     * @param array<non-empty-string|int, mixed> $tokens
     *
     * @return non-empty-string
     */
    public function generate(array $tokens): string;
}
