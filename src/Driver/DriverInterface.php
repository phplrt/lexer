<?php

/**
 * This file is part of phplrt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phplrt\Lexer\Driver;

use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\ReadableInterface;

/**
 * Interface DriverInterface
 */
interface DriverInterface
{
    /**
     * @var string
     */
    public const UNKNOWN_TOKEN_NAME = 'T_UNKNOWN';

    /**
     * @param array $tokens
     * @param ReadableInterface $source
     * @param int $offset
     * @return iterable|TokenInterface[]
     */
    public function run(array $tokens, ReadableInterface $source, int $offset = 0): iterable;

    /**
     * @return void
     */
    public function reset(): void;
}
