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
 * Interface StateBuilderInterface
 */
interface StateBuilderInterface
{
    /**
     * @param string ...$tokens
     * @return StateBuilderInterface|$this
     */
    public function skip(string ...$tokens): self;

    /**
     * @param string $name
     * @param string $pattern
     * @param string|null $newState
     * @return StateBuilderInterface|$this
     */
    public function token(string $name, string $pattern, ?string $newState = null): self;

    /**
     * @param array $tokens
     * @return StateBuilderInterface|$this
     */
    public function tokens(array $tokens): self;

    /**
     * @param string $token
     * @param string $newState
     * @return StateBuilderInterface|$this
     */
    public function jump(string $token, string $newState): self;
}
