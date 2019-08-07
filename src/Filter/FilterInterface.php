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

/**
 * Interface FilterInterface
 */
interface FilterInterface extends LexerInterface
{
    /**
     * FilterInterface constructor.
     *
     * @param LexerInterface $lexer
     */
    public function __construct(LexerInterface $lexer);
}
