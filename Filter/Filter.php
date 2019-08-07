<?php
/**
 * This file is part of lexer package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer\Filter;

use Phplrt\Lexer\LexerInterface;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Contracts\Lexer\Exception\LexerExceptionInterface;
use Phplrt\Contracts\Lexer\Exception\RuntimeExceptionInterface;

/**
 * Class Filter
 */
abstract class Filter implements FilterInterface
{
    /**
     * @var LexerInterface
     */
    protected $parent;

    /**
     * Filter constructor.
     *
     * @param LexerInterface $lexer
     */
    public function __construct(LexerInterface $lexer)
    {
        $this->parent = $lexer;
    }

    /**
     * @param int $token
     * @return string
     */
    public function nameOf(int $token): string
    {
        return $this->parent->nameOf($token);
    }

    /**
     * @param ReadableInterface $source
     * @return iterable|TokenInterface[]
     * @throws LexerExceptionInterface
     * @throws RuntimeExceptionInterface
     */
    public function lex(ReadableInterface $source): iterable
    {
        return $this->parent->lex($source);
    }
}
