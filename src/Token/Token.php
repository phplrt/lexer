<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Token;

use Phplrt\Lexer\Driver\DriverInterface;
use Phplrt\Contracts\Lexer\TokenInterface;

class Token extends BaseToken
{
    /**
     * @var int<0, max>
     */
    private static int $anonymousId = 0;

    /**
     * @var non-empty-string|int<0, max>
     */
    private $name;

    /**
     * @param string|int<0, max> $name
     * @param int<0, max> $offset
     */
    public function __construct($name, private string $value, private int $offset)
    {
        if ($name === '') {
            $name = self::$anonymousId++;
        }

        $this->name = $name;
    }

    public static function empty(): TokenInterface
    {
        return new self(DriverInterface::UNKNOWN_TOKEN_NAME, '', 0);
    }

    public function getName(): string
    {
        if (\is_string($this->name)) {
            return $this->name;
        }

        return '#' . $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function __toString(): string
    {
        if (\class_exists(Renderer::class)) {
            return (new Renderer())->render($this);
        }

        return $this->getName();
    }
}
