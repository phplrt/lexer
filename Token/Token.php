<?php
/**
 * This file is part of Phplrt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer\Token;

/**
 * Class Token
 */
class Token extends BaseToken
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $value;

    /**
     * @var int
     */
    private $offset;

    /**
     * Token constructor.
     *
     * @param string $name
     * @param string|array $value
     * @param int $offset
     */
    public function __construct(string $name, $value, int $offset = 0)
    {
        $this->name = $name;
        $this->value = (array)$value;
        $this->offset = $offset;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getValue(): string
    {
        return $this->value[0];
    }

    /**
     * @return iterable|string[]
     */
    public function getGroups(): iterable
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }
}
