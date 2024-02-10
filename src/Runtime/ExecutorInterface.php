<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Runtime;

use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\ReadableInterface;

/**
 * @internal This is an internal library interface, please do not use it in your code.
 * @psalm-internal Phplrt\Lexer
 */
interface ExecutorInterface
{
    /**
     * @param int<0, max> $offset
     * @param int<1, max>|null $length
     *
     * @return iterable<TokenInterface>
     */
    public function run(ReadableInterface $source, int $offset, ?int $length): iterable;
}
