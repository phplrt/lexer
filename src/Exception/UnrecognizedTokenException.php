<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Exception;

use Phplrt\Contracts\Lexer\LexerRuntimeExceptionInterface;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Lexer\Printer\PrettyPrinter;

class UnrecognizedTokenException extends \RuntimeException implements LexerRuntimeExceptionInterface
{
    final public const CODE_UNRECOGNIZED_TOKEN = 0x01;

    protected const CODE_LAST = self::CODE_UNRECOGNIZED_TOKEN;

    final public function __construct(
        private readonly ReadableInterface $source,
        private readonly TokenInterface $token,
        string $message,
        int $code = self::CODE_LAST,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function fromToken(
        ReadableInterface $source,
        TokenInterface $token,
        \Throwable $previous = null,
    ): self {
        $message = \vsprintf('Syntax error, unrecognized %s', [
            (new PrettyPrinter())->printToken($token),
        ]);

        return new static($source, $token, $message, self::CODE_UNRECOGNIZED_TOKEN, $previous);
    }

    public function getSource(): ReadableInterface
    {
        return $this->source;
    }

    public function getToken(): TokenInterface
    {
        return $this->token;
    }
}
