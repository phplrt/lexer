<?php
/**
 * This file is part of lexer package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer;

use Phplrt\Lexer\State\Markers;
use Phplrt\Lexer\Token\BaseToken;
use Phplrt\Lexer\Builder\Compiler;
use Phplrt\Lexer\Builder\StateBuilder;
use Phplrt\Lexer\Filter\FilterInterface;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Lexer\Builder\LexerBuilderInterface;
use Phplrt\Lexer\Builder\StateBuilderInterface;
use Phplrt\Contracts\Lexer\Exception\LexerExceptionInterface;
use Phplrt\Contracts\Lexer\Exception\RuntimeExceptionInterface;

/**
 * Class Builder
 */
class Builder implements LexerBuilderInterface
{
    /**
     * @var string
     */
    public const STATE_GLOBAL = 'global';

    /**
     * @var string
     */
    public const STATE_DEFAULT = 'default';

    /**
     * @var StateBuilder
     */
    private $global;

    /**
     * @var StateBuilder
     */
    private $default;

    /**
     * @var array|StateBuilder[]
     */
    private $local = [];

    /**
     * @var array|LexerInterface[]
     */
    private $filters = [];

    /**
     * Lexer constructor.
     *
     * @param array $tokens
     * @param array|string[] $skip
     */
    public function __construct(array $tokens = [], array $skip = [])
    {
        $this->global = $this->createStateBuilder();
        $this->default = $this->bootDefaultState($tokens, $skip);
    }

    /**
     * @return StateBuilderInterface
     */
    private function createStateBuilder(): StateBuilderInterface
    {
        return new StateBuilder();
    }

    /**
     * @param array $tokens
     * @param array $skips
     * @return StateBuilderInterface
     */
    private function bootDefaultState(array $tokens, array $skips): StateBuilderInterface
    {
        $builder = $this->createStateBuilder();
        $builder->tokens($tokens);
        $builder->skip(...$skips);

        return $builder;
    }

    /**
     * @param string|FilterInterface ...$filters
     * @return LexerBuilderInterface|$this
     */
    public function through(string ...$filters): LexerBuilderInterface
    {
        $this->filters = \array_merge($this->filters, $filters);

        return $this;
    }

    /**
     * @param \Closure|string $nameOrCallback
     * @param \Closure|null $then
     * @return LexerBuilderInterface|$this
     */
    public function state($nameOrCallback, \Closure $then = null): LexerBuilderInterface
    {
        [$name, $then] = $nameOrCallback instanceof \Closure
            ? [self::STATE_DEFAULT, $nameOrCallback]
            : [$nameOrCallback, $then];

        $then($this->getState($name));

        return $this;
    }

    /**
     * @param string $name
     * @return StateBuilderInterface
     */
    private function getState(string $name): StateBuilderInterface
    {
        if ($name === self::STATE_GLOBAL) {
            return $this->global;
        }

        if ($name === self::STATE_DEFAULT) {
            return $this->global;
        }

        return $this->local[$name] ?? $this->local[$name] = $this->createStateBuilder();
    }

    /**
     * @param string ...$tokens
     * @return StateBuilderInterface|$this
     */
    public function skip(string ...$tokens): StateBuilderInterface
    {
        $this->global->skip(...$tokens);

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
        $this->global->token($name, $pattern, $newState);

        return $this;
    }

    /**
     * @param array $tokens
     * @return StateBuilderInterface|$this
     */
    public function tokens(array $tokens): StateBuilderInterface
    {
        $this->global->tokens($tokens);

        return $this;
    }

    /**
     * @param string $token
     * @param string $newState
     * @return StateBuilderInterface|$this
     */
    public function jump(string $token, string $newState): StateBuilderInterface
    {
        $this->global->jump($token, $newState);

        return $this;
    }

    /**
     * @param ReadableInterface $source
     * @return iterable|TokenInterface[]|BaseToken[]
     * @throws LexerExceptionInterface
     * @throws RuntimeExceptionInterface
     */
    public function lex(ReadableInterface $source): iterable
    {
        return $this->build()->lex($source);
    }

    /**
     * @return LexerInterface
     */
    public function build(): LexerInterface
    {
        $runtime = $this->compile();

        foreach ($this->filters as $filter) {
            $runtime = new $filter($runtime);
        }

        return $runtime;
    }

    /**
     * @return Lexer
     */
    private function compile(): Lexer
    {
        $compiler = new Compiler($this->global, ...\array_values($this->local));

        $states = [
            self::STATE_DEFAULT => new Markers(
                $compiler->tokensFor($this->default, $this->global),
                $compiler->skipsFor($this->default, $this->global),
                $compiler->jumpsFor($this->default, $this->global)
            ),
        ];

        foreach ($this->local as $name => $state) {
            $states[$name] = new Markers(
                $compiler->tokensFor($state, $this->global),
                $compiler->skipsFor($state, $this->global),
                $compiler->jumpsFor($state, $this->global)
            );
        }

        return new Lexer($states, $compiler->getIdentifiers());
    }
}
