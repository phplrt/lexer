<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Exception;

use Phplrt\Contracts\Lexer\LexerExceptionInterface;

class CompilationException extends \LogicException implements LexerExceptionInterface
{
    final public const CODE_EMPTY_TOKEN_NAME = 0x01;

    final public function __construct(string $message, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function fromEmptyTokenName(string $value): self
    {
        $message = \sprintf('Token name defined by "%s" must not be empty', \addcslashes($value, '"'));

        return new static($message, self::CODE_EMPTY_TOKEN_NAME);
    }
}
