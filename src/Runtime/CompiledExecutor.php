<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Runtime;

use Phplrt\Contracts\Lexer\Channel;
use Phplrt\Contracts\Lexer\ChannelInterface;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Contracts\Source\SourceExceptionInterface;
use Phplrt\Lexer\Token\Composite;
use Phplrt\Lexer\Token\CompositeTokenInterface;
use Phplrt\Lexer\Token\Token;

/**
 * @internal This is an internal library class, please do not use it in your code.
 * @psalm-internal Phplrt\Lexer
 */
class CompiledExecutor implements ExecutorInterface
{
    /**
     * @param non-empty-string $pattern
     * @param list<non-empty-string> $skip
     * @param non-empty-string $unknown
     * @param array<non-empty-string, int|non-empty-string> $aliases
     */
    public function __construct(
        public readonly string $pattern,
        public readonly array $skip,
        public readonly string $unknown,
        public readonly array $aliases,
        public readonly bool $composite,
    ) {
    }

    /**
     * @throws SourceExceptionInterface
     */
    public function run(ReadableInterface $source, int $offset, ?int $length): iterable
    {
        $contents = $source->getContents();

        //
        // Truncate the contents of the source if the argument
        // of the size of the parsed file was passed.
        //
        if ($length !== null) {
            $contents = \substr($contents, 0, $length);
        }

        \preg_match_all($this->pattern, $contents, $matches, \PREG_SET_ORDER, $offset);

        $result = [];

        foreach ($matches as $payload) {
            $name = $payload['MARK'];

            // Unwrap alias if exists.
            //
            // Note that "isset" is faster than:
            // - array_key_exist($name, $this->aliases)
            // - $name = $this->aliases[$name] ?? $name;
            // etc...
            //
            if (isset($this->aliases[$name])) {
                $name = $this->aliases[$name];
            }

            $channel = \in_array($name, $this->skip, true)
                ? Channel::HIDDEN
                : Channel::DEFAULT;

            $item = $this->make($name, $offset, $payload, $channel);

            $offset += $item->getBytes();

            \Fiber::getCurrent() && \Fiber::suspend();

            $result[] = $item;
        }

        \Fiber::getCurrent() && \Fiber::suspend();

        return $result;
    }

    /**
     * @param non-empty-string|int $name
     * @param int<0, max> $offset
     * @param list<non-empty-string> $payload
     */
    private function make(string|int $name, int $offset, array $payload, ChannelInterface $channel): TokenInterface
    {
        if ($this->composite && \count($payload) > 2) {
            return $this->composite($name, $payload, $offset, $channel);
        }

        return new Token($name, (string)$payload[0], $offset, $channel);
    }

    /**
     * @param non-empty-string|int $name
     * @param non-empty-array<array-key, string> $payload
     * @param int<0, max> $offset
     *
     * @psalm-suppress PossiblyNullReference : Parent token cannot be null
     */
    private function composite(int|string $name, array $payload, int $offset, ChannelInterface $channel): CompositeTokenInterface
    {
        $children = [];
        $body = null;

        foreach ($payload as $index => $value) {
            if ($index === 'MARK') {
                continue;
            }

            if ($body === null) {
                $body = $value;
            } else {
                if (\is_int($index)) {
                    --$index;
                }

                $children[$index] = new Token($index, $value, $offset + \strpos($body, $value), $channel);
            }
        }

        return new Composite($children, $name, $body, $offset, $channel);
    }
}
