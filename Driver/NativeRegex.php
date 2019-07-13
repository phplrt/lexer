<?php
/**
 * This file is part of Phplrt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer\Driver;

use Phplrt\Lexer\Token\Token;
use Phplrt\Lexer\Token\Unknown;
use Phplrt\Contracts\Io\Readable;
use Phplrt\Lexer\Token\EndOfInput;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Lexer\Iterator\RegexNamedGroupsIterator;
use Phplrt\Lexer\Driver\NativeStateful\PCRECompiler;

/**
 * Class NativeRegex
 */
class NativeRegex extends SimpleLexer
{
    /**
     * NativeRegex constructor.
     *
     * @param array $tokens
     * @param array $skip
     */
    public function __construct(array $tokens = [], array $skip = [])
    {
        $this->tokens = $tokens;
        $this->skipped = $skip;
    }

    /**
     * @param Readable $file
     * @return \Traversable
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function exec(Readable $file): \Traversable
    {
        $offset = 0;
        $regex = new RegexNamedGroupsIterator($this->getPattern(), $file->getContents());

        $iterator = $regex->getIterator();

        while ($iterator->valid()) {
            /** @var TokenInterface $token */
            $token = $iterator->key() === Unknown::T_NAME
                ? $this->unknown($iterator, $offset)
                : $this->token($iterator, $offset);

            $offset += $token->getBytes();

            yield $token;
        }

        yield new EndOfInput($offset);
    }

    /**
     * @return string
     */
    private function getPattern(): string
    {
        return (new PCRECompiler($this->tokens))->compile();
    }

    /**
     * @param \Traversable|\Generator $iterator
     * @param int $offset
     * @return Unknown
     */
    private function unknown(\Traversable $iterator, int $offset): TokenInterface
    {
        $body = $iterator->current()[0];
        $iterator->next();

        $body .= $this->reduce($iterator, Unknown::T_NAME);

        return new Unknown($body, $offset);
    }

    /**
     * @param \Traversable|\Iterator $iterator
     * @param string $key
     * @return string
     */
    protected function reduce(\Traversable $iterator, string $key): string
    {
        $body = '';

        while ($iterator->valid()) {
            if ($iterator->key() !== $key) {
                break;
            }

            $body .= $iterator->current()[0];

            $iterator->next();
        }

        return $body;
    }

    /**
     * @param \Traversable|\Iterator $iterator
     * @param int $offset
     * @return Token
     */
    private function token(\Traversable $iterator, int $offset): TokenInterface
    {
        [$name, $context] = [$iterator->key(), $iterator->current()];

        $iterator->next();

        return new Token($name, $context, $offset);
    }
}
