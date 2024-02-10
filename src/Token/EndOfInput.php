<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Token;

use Phplrt\Contracts\Lexer\Channel;
use Phplrt\Contracts\Lexer\ChannelInterface;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Lexer\Printer\PrettyPrinter;

final class EndOfInput implements TokenInterface, \Stringable
{
    /**
     * @var non-empty-string
     */
    final public const EOI_NAME = 'T_EOI';

    /**
     * @var non-empty-string
     */
    final public const EOI_VALUE = "\0";

    /**
     * @param int<0, max> $offset
     * @param non-empty-string $name
     */
    public function __construct(
        private readonly int $offset = 0,
        private readonly string $name = self::EOI_NAME,
    ) {}

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
        return self::EOI_VALUE;
    }

    public function getBytes(): int
    {
        return 0;
    }

    public function getChannel(): ChannelInterface
    {
        return Channel::EOI;
    }

    public function __toString(): string
    {
        $instance = PrettyPrinter::getInstance();

        return $instance->printToken($this);
    }
}
