<?php
/**
 * This file is part of lexer package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer\State;

/**
 * Class State
 */
abstract class State implements StateInterface
{
    /**
     * @var array|string[]
     */
    protected $tokens;

    /**
     * @var array|string[]
     */
    protected $skip;

    /**
     * @var array
     */
    protected $jumps;

    /**
     * State constructor.
     *
     * @param array|string[] $tokens
     * @param array $skip
     * @param array $jumps
     */
    public function __construct(array $tokens = [], array $skip = [], array $jumps = [])
    {
        $this->tokens = $tokens;
        $this->skip = $skip;
        $this->jumps = $jumps;
    }
}
