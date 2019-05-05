<?php
/**
 * This file is part of Phplrt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer\Driver;

use Phplrt\Io\Readable;
use Phplrt\Lexer\Definition\TokenDefinition;
use Phplrt\Lexer\LexerInterface;
use Phplrt\Lexer\SimpleLexerInterface;
use Phplrt\Lexer\TokenInterface;

/**
 * Class BaseLexer
 */
abstract class SimpleLexer implements SimpleLexerInterface
{
    /**
     * @var array|string[]
     */
    protected $skipped = [];

    /**
     * @var array|string[]
     */
    protected $tokens = [];

    /**
     * @param Readable $input
     * @return \Traversable|TokenInterface[]
     */
    public function lex(Readable $input): \Traversable
    {
        foreach ($this->exec($input) as $token) {
            if (! \in_array($token->getName(), $this->skipped, true)) {
                yield $token;
            }
        }
    }

    /**
     * @param string $token
     * @param string $pcre
     * @return LexerInterface
     */
    public function add(string $token, string $pcre): LexerInterface
    {
        $this->tokens[$token] = $pcre;

        return $this;
    }

    /**
     * @param string $name
     * @return LexerInterface
     */
    public function skip(string $name): LexerInterface
    {
        $this->skipped[] = $name;

        return $this;
    }

    /**
     * @param Readable $file
     * @return \Traversable|TokenInterface[]
     */
    abstract protected function exec(Readable $file): \Traversable;

    /**
     * @return iterable|TokenDefinition[]
     */
    public function getTokenDefinitions(): iterable
    {
        foreach ($this->tokens as $name => $pcre) {
            yield new TokenDefinition($name, $pcre, ! \in_array($name, $this->skipped, true));
        }
    }
}
