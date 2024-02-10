<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Token;

use Phplrt\Contracts\Lexer\Channel;
use Phplrt\Contracts\Lexer\ChannelInterface;
use Phplrt\Contracts\Lexer\TokenInterface;

/**
 * @template-implements \IteratorAggregate<array-key, TokenInterface>
 */
final class Composite extends Token implements \IteratorAggregate, CompositeTokenInterface
{
    /**
     * @var non-empty-array<array-key, TokenInterface>
     */
    private readonly array $children;

    /**
     * @param non-empty-string|int $name
     * @param int<0, max> $offset
     * @param non-empty-array<array-key, TokenInterface> $children
     */
    public function __construct(
        array $children,
        string|int $name,
        string $value,
        int $offset = 0,
        ChannelInterface $channel = Channel::DEFAULT,
    ) {
        parent::__construct($name, $value, $offset, $channel);

        $this->children = $children;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->children[$offset]);
    }

    public function offsetGet($offset): ?TokenInterface
    {
        return $this->children[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        throw new \BadMethodCallException(self::class . ' objects are immutable');
    }

    public function offsetUnset($offset): void
    {
        throw new \BadMethodCallException(self::class . ' objects are immutable');
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->children);
    }

    public function count(): int
    {
        return \count($this->children);
    }
}
