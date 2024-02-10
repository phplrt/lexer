<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Exception;

use Phplrt\Lexer\Multistate\Multistate;

/**
 * @psalm-import-type LexerStateType from Multistate
 * @phpstan-import-type LexerStateType from Multistate
 */
class StateException extends \LogicException implements MultistateLexerExceptionInterface
{
    public const CODE_EMPTY_STATES = 0x01;
    public const CODE_EMPTY_TRANSITIONS = 0x02;
    public const CODE_INVALID_STATE = 0x03;

    protected const CODE_LAST = self::CODE_INVALID_STATE;

    final public function __construct(string $message, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function fromEmptyStates(): self
    {
        $message = 'No state defined for the selected multistate lexer';

        return new self($message, self::CODE_EMPTY_STATES);
    }

    public static function fromEmptyTransitions(): self
    {
        $message = 'No state transition defined for the multistate lexer';

        return new static($message, self::CODE_EMPTY_TRANSITIONS);
    }

    /**
     * @param LexerStateType $state
     */
    public static function fromInvalidState(string|int $state): self
    {
        $message = 'Unrecognized token state #%s';
        $message = \sprintf($message, $state);

        return new static($message, self::CODE_INVALID_STATE);
    }
}
