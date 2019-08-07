<?php
/**
 * This file is part of lexer package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer\Builder;

use Phplrt\Lexer\LexerInterface;
use Phplrt\Lexer\Filter\FilterInterface;
use Phplrt\Contracts\Lexer\LexerInterface as LexerRuntimeInterface;

/**
 * Interface LexerBuilderInterface
 */
interface LexerBuilderInterface extends StateBuilderInterface, LexerRuntimeInterface
{
    /**
     * @param string|\Closure $nameOrCallback
     * @param \Closure|null $then
     * @return LexerBuilderInterface|$this
     */
    public function state($nameOrCallback, \Closure $then = null): self;

    /**
     * @param string|FilterInterface ...$filters
     * @return LexerBuilderInterface|$this
     */
    public function through(string ...$filters): self;

    /**
     * @return LexerInterface
     */
    public function build(): LexerInterface;
}
