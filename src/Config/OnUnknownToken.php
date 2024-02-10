<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Config;

use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Lexer\Exception\UnrecognizedTokenException;
use Phplrt\Lexer\Token\UnknownToken;

enum OnUnknownToken implements HandlerInterface
{
    /**
     * If this case (that is {@see OnUnknownToken::THROW}) is used, then, when
     * an error (incorrect token) is found, the corresponding
     * exception {@see UnrecognizedTokenException} will be thrown.
     */
    case THROW;

    /**
     * If this case (that is {@see OnUnknownToken::RETURN}) is used, then the
     * {@see UnknownToken} token will be returned "as is" inside the parsed result.
     */
    case RETURN;

    /**
     * If this case (that is {@see OnUnknownToken::SKIP}) is used, then the
     * {@see UnknownToken} token will be skipped from the result.
     *
     * This behavior can be important when implementing tolerant parsers.
     */
    case SKIP;

    /**
     * @template TToken of TokenInterface
     *
     * @param TToken $token
     *
     * @return TToken|null
     * @throws UnrecognizedTokenException
     */
    public function handle(ReadableInterface $source, TokenInterface $token): ?TokenInterface
    {
        return match ($this) {
            self::SKIP => null,
            self::RETURN => $token,
            default => throw UnrecognizedTokenException::fromToken($source, $token)
        };
    }
}
