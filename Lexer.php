<?php
/**
 * This file is part of phplrt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer;

use Phplrt\Lexer\Token\Skip;
use Phplrt\Lexer\Token\Unknown;
use Phplrt\Lexer\State\Markers;
use Phplrt\Lexer\Token\BaseToken;
use Phplrt\Lexer\Token\EndOfInput;
use Phplrt\Lexer\State\StateInterface;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Lexer\Exception\LexerException;
use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Lexer\Exception\LexerRuntimeException;
use Phplrt\Lexer\Exception\UnexpectedTokenException;
use Phplrt\Lexer\Exception\UnexpectedStateException;
use Phplrt\Lexer\Exception\EndlessRecursionException;
use Phplrt\Contracts\Source\Exception\NotReadableExceptionInterface;

/**
 * Class Lexer
 */
class Lexer implements LexerInterface
{
    /**
     * @var string
     */
    private const DEFAULT_STATE_DRIVER = Markers::class;

    /**
     * @var string
     */
    private const ERROR_EMPTY_STATES = 'Can not start lexical analysis, because lexer was not initialized';

    /**
     * @var string
     */
    private const ERROR_UNEXPECTED_TOKEN = 'Syntax error, unexpected %s (%s)';

    /**
     * @var string
     */
    private const ERROR_ENDLESS_TRANSITIONS = 'An unsolvable infinite lexer state transitions was found %s';

    /**
     * @var string
     */
    private const ERROR_UNEXPECTED_STATE = 'Unrecognized token state #%s';

    /**
     * @var string
     */
    private const ERROR_STATE_DATA = 'Lexer state #%s data should be an array or instance of %s, but %s given';

    /**
     * @var array|StateInterface[]
     */
    protected $states = [];

    /**
     * @var array|string[]
     */
    protected $tokens = [];

    /**
     * @var string|int|null
     */
    protected $initial;

    /**
     * Lexer constructor.
     *
     * @param array|StateInterface[]|array[] $states
     * @param array|string[] $tokens
     * @param null|string|int $initial
     */
    public function __construct(array $states = [], array $tokens = [], $initial = null)
    {
        $this->initial = $initial ?? $this->initial;

        /** @noinspection AdditionOperationOnArraysInspection */
        $this->tokens += $tokens;

        /** @noinspection AdditionOperationOnArraysInspection */
        $this->states = $this->bootStates($this->states += $states);
    }

    /**
     * @param array $states
     * @return array
     */
    private function bootStates(array $states): array
    {
        $result = [];

        foreach ($states as $id => $data) {
            $result[$id] = $this->createState($data, $id);
        }

        if (\count($result) === 0) {
            throw new LexerException(self::ERROR_EMPTY_STATES);
        }

        return $result;
    }

    /**
     * @param mixed $payload
     * @param $id
     * @return StateInterface
     */
    private function createState($payload, $id): StateInterface
    {
        $driver = self::DEFAULT_STATE_DRIVER;

        switch (true) {
            case $payload instanceof StateInterface:
                return $payload;

            case \is_array($payload):
                return new $driver(...\array_values($payload));

            default:
                $message = \sprintf(self::ERROR_STATE_DATA, $id, StateInterface::class, \gettype($payload));
                throw new LexerException($message);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @param ReadableInterface $source
     * @return TokenInterface[]|BaseToken[]
     * @throws LexerException
     * @throws NotReadableExceptionInterface
     */
    public function lex(ReadableInterface $source): iterable
    {
        $stream = $this->execute($source, $this->getInitialStateIdentifier(), $source->getContents());

        while ($stream->valid()) {
            try {
                /** @var TokenInterface $token */
                $token = $stream->current();

                if ($token->getType() === Unknown::ID) {
                    throw $this->unexpectedTokenException($source, $token);
                }

                yield $token;

                $stream->next();
            } catch (LexerRuntimeException $exception) {
                throw $exception->onToken($source, $token);
            }
        }

        yield new EndOfInput(isset($token) ? $token->getOffset() + $token->getBytes() : 0);
    }

    /**
     * @return int|mixed|string
     */
    private function getInitialStateIdentifier()
    {
        if ($this->initial !== null && isset($this->states[$this->initial])) {
            return $this->initial;
        }

        return \array_key_first($this->states);
    }

    /**
     * @param ReadableInterface $src
     * @param int $state
     * @param string $content
     * @param int $offset
     * @return \Generator|TokenInterface[]
     * @throws NotReadableExceptionInterface
     * @throws LexerRuntimeException
     */
    private function execute(ReadableInterface $src, int $state, string $content, int $offset = 0): \Generator
    {
        /**
         * We save the offset for the state to prevent endless transitions
         * in the future.
         */
        $states[$state] = $offset;

        execution:

        /**
         * Checking the existence of the current state.
         */
        if (! isset($this->states[$state])) {
            throw $this->unexpectedStateException($src, $state, $offset);
        }

        /**
         * This cycle is necessary in order to capture the last token,
         * because in PHP, "local "loop variables have a function scope.
         *
         * That is, the "$token" variable will be available in the future.
         */
        foreach ($stream = $this->states[$state]->execute($content, $offset) as $token) {
            yield $token;
        }

        /**
         * If the generator returns something like integer, it means a
         * forced transition to a new state.
         *
         * @noinspection CallableParameterUseCaseInTypeContextInspection
         */
        if (\is_int($state = $stream->getReturn())) {

            /**
             * If the same offset is repeatedly detected for this state,
             * then at this stage there was an entrance to an endless cycle.
             */
            if (($states[$state] ?? null) === $offset) {
                $exception = $this->endlessTransitionsException($offset, $token ?? null);
                $exception->throwsIn($src, $offset);

                if (isset($token)) {
                    $exception->onToken($src, $token);
                }

                throw $exception;
            }

            $states[$state] = $offset;

            /**
             * If at least one token has been returned at the moment, then
             * further analysis should be continued already from the
             * desired offset.
             */
            if (isset($token)) {
                $offset = $token->getOffset() + $token->getBytes();
            }

            /**
             * The label expression used to reduce recursive invocation, like:
             *
             * <code>
             *  yield from $this->execute($src, $state, $content, $offset);
             * </code>
             *
             * In this case, the call stack remains unchanged and cannot be
             * overflowed. Otherwise, you may get an error like:
             * "Maximum function nesting level of '100' reached, aborting!".
             */
            goto execution;
        }
    }

    /**
     * An error that occurs when the required state was not found.
     *
     * @param ReadableInterface $src
     * @param int $state
     * @param int $offset
     * @return UnexpectedStateException
     * @throws NotReadableExceptionInterface
     */
    private function unexpectedStateException(ReadableInterface $src, int $state, int $offset): UnexpectedStateException
    {
        $exception = new UnexpectedStateException(\sprintf(self::ERROR_UNEXPECTED_STATE, $state));
        $exception->throwsIn($src, $offset);

        return $exception;
    }

    /**
     * An error that occurs when an infinite set of state transitions is detected.
     *
     * @param int $offset
     * @param TokenInterface|null $token
     * @return EndlessRecursionException
     */
    private function endlessTransitionsException(int $offset, TokenInterface $token = null): EndlessRecursionException
    {
        switch (true) {
            case $offset === 0:
                $message = 'at start of the source data';
                break;

            case $token !== null:
                $message = \sprintf('near %s (%s)', $token, $this->nameOf($token->getType()));
                break;

            default:
                $message = '';
        }

        return new EndlessRecursionException(\sprintf(self::ERROR_ENDLESS_TRANSITIONS, $message));
    }

    /**
     * @param int $token
     * @return string
     */
    public function nameOf(int $token): string
    {
        switch ($token) {
            case Skip::ID:
                return Skip::NAME;

            case EndOfInput::ID;
                return EndOfInput::NAME;

            default:
                return $this->tokens[$token] ?? Unknown::NAME;
        }
    }

    /**
     * @param ReadableInterface $src
     * @param TokenInterface $token
     * @return UnexpectedTokenException
     * @throws NotReadableExceptionInterface
     */
    private function unexpectedTokenException(ReadableInterface $src, TokenInterface $token): UnexpectedTokenException
    {
        $message = \sprintf(self::ERROR_UNEXPECTED_TOKEN, $token, $this->nameOf($token->getType()));

        $exception = new UnexpectedTokenException($message);
        $exception->onToken($src, $token);

        return $exception;
    }
}
