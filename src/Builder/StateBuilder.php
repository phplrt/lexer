<?php
/**
 * This file is part of lexer package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer\Builder;

/**
 * Class StateBuilder
 */
class StateBuilder implements StateBuilderInterface
{
    /**
     * @var array|string[]
     */
    private $tokens = [];

    /**
     * @var array|string[]
     */
    private $jumps = [];

    /**
     * @var array|string[]
     */
    private $skips = [];

    /**
     * @var \Closure
     */
    private $onChange;

    /**
     * StateBuilder constructor.
     *
     * @param \Closure|null $onChange
     */
    public function __construct(\Closure $onChange = null)
    {
        // @formatter:off
        $this->onChange = $onChange ?? static function () {};
        // @formatter:on
    }

    /**
     * @param string ...$tokens
     * @return StateBuilderInterface|$this
     */
    public function skip(string ...$tokens): StateBuilderInterface
    {
        ($this->onChange)($this);

        $this->skips = \array_merge($this->skips, $tokens);

        return $this;
    }

    /**
     * @param string $name
     * @param string $pattern
     * @param string|null $newState
     * @return StateBuilderInterface|$this
     */
    public function token(string $name, string $pattern, ?string $newState = null): StateBuilderInterface
    {
        ($this->onChange)($this);

        $this->tokens[$name] = $pattern;

        return $newState !== null ? $this->jump($name, $newState) : $this;
    }

    /**
     * @param array $tokens
     * @return StateBuilderInterface
     */
    public function tokens(array $tokens): StateBuilderInterface
    {
        /** @noinspection AdditionOperationOnArraysInspection */
        $this->tokens += $tokens;

        return $this;
    }

    /**
     * @param string $token
     * @param string $newState
     * @return StateBuilderInterface|$this
     */
    public function jump(string $token, string $newState): StateBuilderInterface
    {
        $this->jumps[$token] = $newState;

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /**
     * @return array|string[]
     */
    public function getJumps(): array
    {
        return $this->jumps;
    }

    /**
     * @return array|string[]
     */
    public function getSkips(): array
    {
        return $this->skips;
    }
}
