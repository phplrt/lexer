<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Config;

use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Lexer\Token\EndOfInput;

enum OnEndOfInput implements HandlerInterface
{
    /**
     * If this case (that is {@see OnEndOfInput::RETURN}) is used, then the
     * {@see EndOfInput} token will be returned "as is" at the end of the
     * parsed code.
     */
    case RETURN;

    /**
     * If this case (that is {@see OnEndOfInput::SKIP}) is used, then the
     * {@see EndOfInput} token will be skipped at the end of the result.
     */
    case SKIP;

    /**
     * @template TToken of TokenInterface
     *
     * @param TToken $token
     *
     * @return TToken|null
     */
    public function handle(ReadableInterface $source, TokenInterface $token): ?TokenInterface
    {
        if ($this === self::SKIP) {
            return null;
        }

        return $token;
    }
}
