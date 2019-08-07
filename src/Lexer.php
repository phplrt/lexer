<?php
/**
 * This file is part of lexer package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer;

use Phplrt\Lexer\Token\Skip;
use Phplrt\Lexer\Token\Unknown;
use Phplrt\Lexer\Token\BaseToken;
use Phplrt\Lexer\Token\EndOfInput;
use Phplrt\Lexer\State\StateInterface;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Lexer\Exception\LexerException;
use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Lexer\Exception\LexerRuntimeException;
use Phplrt\Contracts\Source\Exception\NotReadableExceptionInterface;

/**
 * Class Runtime
 */
final class Lexer implements LexerInterface
{
    /**
     * @var string
     */
    private const ERROR_EMPTY_STATES = 'Can not start lexical analysis, because lexer was not initialized';

    /**
     * @var string
     */
    private const ERROR_BAD_STATE = 'Unrecognized lexer state "%s"';

    /**
     * @var StateInterface[]
     */
    private $states;

    /**
     * @var array|string[]
     */
    private $tokens;

    /**
     * Lexer constructor.
     *
     * @param array|StateInterface[]|StateInterface $states
     * @param array|string[] $tokens
     */
    public function __construct(array $states, array $tokens = [])
    {
        $this->states = $states;
        $this->tokens = $tokens;
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
     * {@inheritDoc}
     *
     * @param ReadableInterface $source
     * @return TokenInterface[]|BaseToken[]
     * @throws LexerException
     * @throws NotReadableExceptionInterface
     */
    public function lex(ReadableInterface $source): iterable
    {
        if (\count($this->states) === 0) {
            throw new LexerException(self::ERROR_EMPTY_STATES);
        }

        $stream = $this->execute($this->getInitialStateName(), $source->getContents());

        while ($stream->valid()) {
            try {
                /** @var TokenInterface $token */
                yield $token = $stream->current();

                $stream->next();
            } catch (LexerRuntimeException $exception) {
                throw $exception->onToken($source, $token);
            }
        }

        yield new EndOfInput(isset($token) ? $token->getOffset() + $token->getBytes() : 0);
    }

    /**
     * @param string $state
     * @param string $content
     * @param int $offset
     * @return \Generator|TokenInterface[]
     * @throws LexerRuntimeException
     */
    private function execute(string $state, string $content, int $offset = 0): \Generator
    {
        $stream = $this->getState($state)->execute($content, $offset);

        foreach ($stream as $token) {
            yield $token;
        }

        if (isset($token)) {
            $offset = $token->getOffset() + $token->getBytes();
        }

        if (\is_string($next = $stream->getReturn())) {
            yield from $this->execute((string)$next, $content, $offset);
        }
    }

    /**
     * @param string $name
     * @return StateInterface
     * @throws LexerRuntimeException
     */
    private function getState(string $name): StateInterface
    {
        $state = $this->states[$name] ?? null;

        if (! $state instanceof StateInterface) {
            throw new LexerRuntimeException(\sprintf(self::ERROR_BAD_STATE, $name));
        }

        return $state;
    }

    /**
     * @return string
     */
    private function getInitialStateName(): string
    {
        return \array_keys($this->states)[0];
    }
}
