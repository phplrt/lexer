<?php

declare(strict_types=1);

namespace Phplrt\Lexer;

use Phplrt\Contracts\Lexer\Channel;
use Phplrt\Contracts\Lexer\LexerInterface;
use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Lexer\Exception\StateException;
use Phplrt\Lexer\Exception\TransitionException;

/**
 * @psalm-type LexerStateType = non-empty-string|int<0, max>
 * @phpstan-type LexerStateType non-empty-string|int<0, max>
 */
final class Multistate implements LexerInterface
{
    /**
     * @var LexerStateType
     */
    private readonly int|string $state;

    /**
     * @param LexerInterface $states
     * @param non-empty-array<LexerStateType, non-empty-array<non-empty-string, LexerStateType>> $transitions
     * @param LexerStateType|null $state
     */
    public function __construct(
        private readonly array $states = [],
        private readonly array $transitions = [],
        int|string $state = null,
    ) {
        if ($this->states === []) {
            throw StateException::fromEmptyStates();
        }

        if ($this->transitions === []) {
            throw StateException::fromEmptyTransitions();
        }

        $this->state = $state ?? \array_key_first($this->states);
    }

    public function lex(ReadableInterface $source, int $offset = 0, int $length = null): iterable
    {
        $states = [];
        $state = null;

        do {
            $completed = true;

            /**
             * We save the offset for the state to prevent endless transitions
             * in the future.
             */
            $states[$state ??= $this->state] = $offset;

            /**
             * Checking the existence of the current state.
             */
            if (!isset($this->states[$state])) {
                if (isset($token)) {
                    throw TransitionException::fromInvalidTransition($source, $token, $state);
                }

                throw StateException::fromInvalidState($state);
            }

            $stream = $this->states[$state]->lex($source, $offset, $length);

            /**
             * This cycle is necessary in order to capture the last token,
             * because in PHP, "local "loop variables have a function scope.
             *
             * That is, the "$token" variable will be available in the future.
             */
            foreach ($stream as $token) {
                yield $token;

                if ($token->getChannel() === Channel::EOI) {
                    return;
                }

                /**
                 * If there is a transition, then update the data and start lexing again.
                 *
                 * @var int|string $next
                 */
                if (($next = ($this->transitions[$state][$token->getName()] ?? null)) !== null) {
                    /**
                     * If at least one token has been returned at the moment, then
                     * further analysis should be continued already from the
                     * desired offset and state.
                     */
                    $state = $next;

                    $offset = $token->getBytes() + $token->getOffset();

                    /**
                     * If the same offset is repeatedly detected for this state,
                     * then at this stage there was an entrance to an endless cycle.
                     */
                    if (($states[$state] ?? null) === $offset) {
                        throw TransitionException::fromEndlessRecursion($source, $token, $state);
                    }

                    $completed = false;

                    continue 2;
                }
            }
        } while (!$completed);
    }
}
