<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Token;

use Phplrt\Contracts\Lexer\Channel;
use Phplrt\Contracts\Lexer\ChannelInterface;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Lexer\Printer\PrettyPrinter;

class UnknownToken implements TokenInterface, \Stringable
{
    /**
     * @var non-empty-string
     */
    final public const UNKNOWN_NAME = 'T_UNKNOWN';

    /**
     * @var int<0, max>
     */
    private readonly int $bytes;

    /**
     * @param int<0, max> $offset
     * @param non-empty-string $name
     */
    public function __construct(
        private readonly string $value,
        private readonly int $offset = 0,
        private readonly string $name = self::UNKNOWN_NAME,
    ) {
        $this->bytes = \strlen($this->value);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getChannel(): ChannelInterface
    {
        return Channel::UNKNOWN;
    }

    public function getBytes(): int
    {
        return $this->bytes;
    }

    public function __toString(): string
    {
        $instance = PrettyPrinter::getInstance();

        return $instance->printToken($this);
    }
}
