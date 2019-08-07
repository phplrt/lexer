<?php
/**
 * This file is part of lexer package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer\Filter;

use Phplrt\Lexer\Token\Skip;
use Phplrt\Lexer\LexerInterface;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Contracts\Lexer\Exception\LexerExceptionInterface;
use Phplrt\Contracts\Lexer\Exception\RuntimeExceptionInterface;

/**
 * Class IgnoreSkips
 */
class IgnoreSkips implements LexerInterface
{
    /**
     * @var LexerInterface
     */
    private $lexer;

    /**
     * IgnoreSkips constructor.
     *
     * @param LexerInterface $lexer
     */
    public function __construct(LexerInterface $lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * @param ReadableInterface $source
     * @return iterable|TokenInterface[]
     * @throws LexerExceptionInterface
     * @throws RuntimeExceptionInterface
     */
    public function lex(ReadableInterface $source): iterable
    {
        foreach ($this->lexer->lex($source) as $token) {
            if ($token->getType() !== Skip::ID) {
                yield $token;
            }
        }
    }

    /**
     * @param int $token
     * @return string
     */
    public function nameOf(int $token): string
    {
        return $this->lexer->nameOf($token);
    }
}
