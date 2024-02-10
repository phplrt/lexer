<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Exception;

use Phplrt\Contracts\Lexer\LexerRuntimeExceptionInterface;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Lexer\Multistate\Multistate;

/**
 * @psalm-import-type LexerStateType from Multistate
 * @phpstan-import-type LexerStateType from Multistate
 */
class TransitionException extends \RuntimeException implements
    MultistateLexerExceptionInterface,
    LexerRuntimeExceptionInterface
{
    public const CODE_INVALID_TRANSITION = 0x01;
    public const CODE_ENDLESS_TRANSITIONS = 0x02;

    protected const CODE_LAST = self::CODE_ENDLESS_TRANSITIONS;

    final public function __construct(
        private readonly ReadableInterface $source,
        private readonly TokenInterface $token,
        string $message,
        int $code = self::CODE_LAST,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param LexerStateType $state
     */
    public static function fromInvalidTransition(
        ReadableInterface $source,
        TokenInterface $token,
        string|int $state,
    ): self {
        $message = 'Cannot change lexer state to #%s because this state is invalid';
        $message = \sprintf($message, $state);

        return new static($source, $token, $message, self::CODE_INVALID_TRANSITION);
    }

    /**
     * @param LexerStateType $state
     */
    public static function fromEndlessRecursion(
        ReadableInterface $source,
        TokenInterface $token,
        string|int $state,
    ): self {
        $message = 'An unsolvable infinite lexer state transitions was found at #%s';
        $message = \sprintf($message, $state);

        return new static($source, $token, $message, self::CODE_ENDLESS_TRANSITIONS);
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
