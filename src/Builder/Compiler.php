<?php
/**
 * This file is part of lexer package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer\Builder;

use Phplrt\Lexer\Exception\LexerException;

/**
 * Class Compiler
 */
final class Compiler
{
    /**
     * @var int
     */
    private const INITIAL_TOKEN_ID = 0xff;

    /**
     * @var string
     */
    private const ERROR_TOKEN_NAME_CONFLICT = 'Token with name "%s" already defined';

    /**
     * @var string
     */
    private const ERROR_BAD_SKIPPING = 'Can not skip a non-defined token "%s"';

    /**
     * @var array|int
     */
    private $ids;

    /**
     * @var array|int[]
     */
    private $states = [];

    /**
     * Compiler constructor.
     *
     * @param StateBuilder ...$states
     */
    public function __construct(StateBuilder ...$states)
    {
        $this->ids = $this->bootIdentifiers(...$states);
    }

    /**
     * @return array|string[]
     */
    public function getIdentifiers(): array
    {
        return \array_flip($this->ids);
    }

    /**
     * @param StateBuilder ...$states
     * @return array|int
     */
    private function bootIdentifiers(StateBuilder ...$states): array
    {
        $ids = [];

        foreach ($states as $state) {
            foreach ($state->getTokens() as $name => $pcre) {
                if (isset($ids[$name])) {
                    throw new LexerException(\sprintf(self::ERROR_TOKEN_NAME_CONFLICT, $name));
                }

                $ids[$name] = \count($ids) + self::INITIAL_TOKEN_ID;
            }
        }

        return $ids;
    }

    /**
     * @param StateBuilder ...$states
     * @return array
     */
    public function tokensFor(StateBuilder ...$states): array
    {
        $result = [];

        foreach ($states as $state) {
            foreach ($state->getTokens() as $token => $pcre) {
                $result[$this->ids[$token]] = $pcre;
            }
        }

        return $result;
    }

    /**
     * @param StateBuilder ...$states
     * @return array
     */
    public function skipsFor(StateBuilder ...$states): array
    {
        $result = [];

        foreach ($states as $state) {
            foreach ($state->getSkips() as $token) {
                if (! isset($this->ids[$token])) {
                    throw new LexerException(\sprintf(self::ERROR_BAD_SKIPPING, $token));
                }

                $result[] = $this->ids[$token];
            }
        }

        return $result;
    }

    /**
     * @param StateBuilder ...$states
     * @return array
     */
    public function jumpsFor(StateBuilder ...$states): array
    {
        $result = [];

        foreach ($states as $state) {
            foreach ($state->getJumps() as $token => $next) {
                $result[$this->ids[$token]] = $next;
            }
        }

        return $result;
    }
}
