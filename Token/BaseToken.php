<?php
/**
 * This file is part of phplrt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer\Token;

use Phplrt\Contracts\Lexer\TokenInterface;

/**
 * Class BaseToken
 */
abstract class BaseToken implements TokenInterface
{
    /**
     * @var int|null
     */
    private $bytes;

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            'id'     => $this->getType(),
            'value'  => $this->getValue(),
            'offset' => $this->getOffset(),
            'length' => $this->getBytes(),
        ];
    }

    /**
     * @return int
     */
    public function getBytes(): int
    {
        return $this->bytes ?? $this->bytes = \strlen($this->getValue());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getValue();
    }
}
