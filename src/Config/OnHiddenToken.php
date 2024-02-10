<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Config;

use Phplrt\Contracts\Lexer\Channel;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\ReadableInterface;

enum OnHiddenToken implements HandlerInterface
{
    /**
     * If this case (that is {@see OnHiddenToken::SKIP}) is used, then
     * the token {@see Token} with {@see Channel::HIDDEN} channel will
     * be skipped.
     */
    case SKIP;

    /**
     * If this case (that is {@see OnHiddenToken::RETURN}) is used, then
     * the {@see Token} with {@see Channel::HIDDEN} channel token will be
     * returned "as is".
     */
    case RETURN;


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
