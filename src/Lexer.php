<?php

/**
 * This file is part of phplrt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phplrt\Lexer;

use Phplrt\Source\File;
use Phplrt\Lexer\Token\Token;
use Phplrt\Lexer\Driver\Markers;
use Phplrt\Lexer\Token\EndOfInput;
use Phplrt\Lexer\Driver\DriverInterface;
use Phplrt\Contracts\Lexer\LexerInterface;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Lexer\Exception\UnrecognizedTokenException;
use Phplrt\Contracts\Lexer\Exception\LexerExceptionInterface;
use Phplrt\Contracts\Lexer\Exception\LexerRuntimeExceptionInterface;

/**
 * Class Lexer
 */
class Lexer implements LexerInterface, MutableLexerInterface
{
    /**
     * @var array|string[]
     */
    protected array $tokens;

    /**
     * @var array|string[]
     */
    protected array $skip;

    /**
     * @var DriverInterface
     */
    private DriverInterface $driver;

    /**
     * Lexer constructor.
     *
     * @param array|string[] $tokens
     * @param array|string[] $skip
     * @param DriverInterface|null $driver
     */
    public function __construct(array $tokens = [], array $skip = [], DriverInterface $driver = null)
    {
        $this->driver = $driver ?? new Markers();
        $this->tokens = $tokens;
        $this->skip = $skip;
    }

    /**
     * @return DriverInterface
     */
    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }

    /**
     * @param DriverInterface $driver
     * @return $this
     */
    public function setDriver(DriverInterface $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @return void
     */
    private function reset(): void
    {
        $this->driver->reset();
    }

    /**
     * @param string ...$names
     * @return MutableLexerInterface|$this
     */
    public function skip(string ...$names): MutableLexerInterface
    {
        $this->skip = \array_merge($this->skip, $names);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function append(string $token, string $pattern): MutableLexerInterface
    {
        $this->reset();
        $this->tokens[$token] = $pattern;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function appendMany(array $tokens): MutableLexerInterface
    {
        $this->reset();
        $this->tokens = \array_merge($this->tokens, $tokens);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(string $token, string $pattern): MutableLexerInterface
    {
        $this->reset();
        $this->tokens = \array_merge([$token, $pattern], $this->tokens);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function prependMany(array $tokens, bool $reverseOrder = true): MutableLexerInterface
    {
        $this->reset();
        $this->tokens = \array_merge($reverseOrder ? \array_reverse($tokens) : $tokens, $this->tokens);

        return $this;
    }

    /**
     * @param resource|string|ReadableInterface $source
     * @param int $offset
     * @return iterable
     * @throws LexerExceptionInterface
     * @throws LexerRuntimeExceptionInterface
     */
    public function lex($source, int $offset = 0): iterable
    {
        $unknown = [];

        foreach ($this->driver->run($this->tokens, File::new($source), $offset) as $token) {
            if (\in_array($token->getName(), $this->skip, true)) {
                continue;
            }

            if ($token->getName() === $this->driver::UNKNOWN_TOKEN_NAME) {
                $unknown[] = $token;
                continue;
            }

            if (\count($unknown) && $token->getName() !== $this->driver::UNKNOWN_TOKEN_NAME) {
                throw new UnrecognizedTokenException($this->reduce($unknown));
            }

            yield $token;
        }

        if (\count($unknown)) {
            throw new UnrecognizedTokenException($this->reduce($unknown));
        }

        yield new EndOfInput(isset($token) ? $token->getOffset() + $token->getBytes() : 0);
    }

    /**
     * @param array|TokenInterface[] $tokens
     * @return TokenInterface
     */
    private function reduce(array $tokens): TokenInterface
    {
        $concat = static function (string $data, TokenInterface $token): string {
            return $data . $token->getValue();
        };

        $value = (string)\array_reduce($tokens, $concat, '');

        return new Token(\reset($tokens)->getName(), $value, \reset($tokens)->getOffset());
    }
}
