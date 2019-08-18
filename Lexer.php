<?php
/**
 * This file is part of phplrt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer;

use Phplrt\Lexer\Token\Token;
use Phplrt\Lexer\Token\Unknown;
use Phplrt\Lexer\State\Markers;
use Phplrt\Lexer\Token\BaseToken;
use Phplrt\Lexer\Token\EndOfInput;
use Phplrt\Lexer\State\StateInterface;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Lexer\Exception\LexerException;
use Phplrt\Contracts\Lexer\LexerInterface;
use Phplrt\Lexer\Exception\LexerRuntimeException;
use Phplrt\Lexer\Exception\UnrecognizedTokenException;
use Phplrt\Lexer\Exception\UnexpectedStateException;
use Phplrt\Lexer\Exception\EndlessRecursionException;

/**
 * Class Lexer
 */
class Lexer extends AbstractLexer
{
    /**
     * Lexer constructor.
     *
     * @param array|StateInterface[]|array[] $states
     * @param null|int $initial
     */
    public function __construct(array $states = [], int $initial = null)
    {
        $this->initial = $initial;
        /** @noinspection AdditionOperationOnArraysInspection */
        $this->states = $states;

        parent::__construct();
    }

    /**
     * @param array $tokens
     * @param array $skip
     * @return Lexer|$this
     */
    public static function create(array $tokens, array $skip = []): self
    {
        return new static([
            [
                $tokens,
                $skip
            ]
        ]);
    }
}
