<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Printer;

use Phplrt\Contracts\Lexer\Channel;
use Phplrt\Contracts\Lexer\TokenInterface;

final class PrettyPrinter extends Printer
{
    /**
     * @var int<1, max>
     */
    private const DEFAULT_LENGTH = 30;

    /**
     * @var non-empty-string
     */
    private const DEFAULT_WRAP = '"';

    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private const DEFAULT_REPLACEMENTS = [
        "\0" => '\0',
        "\n" => '\n',
        "\t" => '\t',
    ];

    /**
     * @var non-empty-string
     */
    private const DEFAULT_OVERFLOW_SUFFIX = ' (%s+)';

    /**
     * @var non-empty-string
     */
    private const DEFAULT_END_OF_INPUT = 'end of input';

    /**
     * @param int<1, max> $length
     * @param array<non-empty-string, non-empty-string> $replacements
     * @param non-empty-string $eoi
     */
    public function __construct(
        private readonly int $length = self::DEFAULT_LENGTH,
        private readonly string $wrapAtStart = self::DEFAULT_WRAP,
        private readonly string $wrapAtEnd = self::DEFAULT_WRAP,
        private readonly array $replacements = self::DEFAULT_REPLACEMENTS,
        private readonly string $suffix = self::DEFAULT_OVERFLOW_SUFFIX,
        private readonly string $eoi = self::DEFAULT_END_OF_INPUT,
    ) {
        self::$instance ??= $this;
    }

    public function printToken(TokenInterface $token): string
    {
        $name = $token->getName();
        $value = $token->getValue();

        //
        // If the name is equal to the value, like a:
        //  object(Token) { name: "=>", value: "=>" }
        //
        if ($name === $value) {
            /** @var non-empty-string */
            return $this->value($value);
        }

        return match ($token->getChannel()) {
            Channel::EOI => $this->printEoiToken($token),
            Channel::UNKNOWN => $this->printUnknownToken($token),
            Channel::DEFAULT => $this->printDefaultToken($token),
            default => $this->printNonDefaultToken($token),
        };
    }

    private function printNonDefaultToken(TokenInterface $token): string
    {
        $name = $token->getName();
        $value = $token->getValue();
        $channel = $token->getChannel();

        return \vsprintf('%s (%s:%s)', [
            $this->value($value),
            $channel->getName(),
            $this->name($name),
        ]);
    }

    private function printDefaultToken(TokenInterface $token): string
    {
        $name = $token->getName();
        $value = $token->getValue();

        if (\is_string($name)) {
            return \vsprintf('%s (%s)', [
                $this->value($value),
                $this->name($name),
            ]);
        }

        return $this->value($value);
    }

    private function printUnknownToken(TokenInterface $token): string
    {
        return $this->value($token->getValue());
    }

    private function printEoiToken(TokenInterface $token): string
    {
        return $this->eoi;
    }

    public function name(string|int $name): string
    {
        if (\is_int($name)) {
            return "#$name";
        }

        return $this->escape($name);
    }

    /**
     * @param string $content
     * @return ($content is non-empty-string ? non-empty-string : string)
     */
    public function value(string $content): string
    {
        $value = $this->escape($this->inline($content));

        if ($this->shouldBeShortened($value)) {
            return $this->cut($value);
        }

        return $this->wrap($value);
    }

    private function escape(string $value): string
    {
        return \addcslashes($value, $this->wrapAtStart . $this->wrapAtEnd);
    }

    private function inline(string $value): string
    {
        $result = \preg_replace('/\h+/u', ' ', $value);

        if (\is_string($result)) {
            return $result;
        }

        return $value;
    }

    private function shouldBeShortened(string $value): bool
    {
        $length = \mb_strlen($value);

        return $length > $this->length + \mb_strlen($this->suffix($value));
    }

    private function suffix(string $value): string
    {
        return \sprintf($this->suffix, \mb_strlen($value) - $this->length);
    }

    private function cut(string $value): string
    {
        $prefix = $this->wrap(\mb_substr($value, 0, $this->length) . '…');

        return $prefix . $this->suffix($value);
    }

    private function wrap(string $value): string
    {
        return $this->wrapAtStart . $this->replace($value) . $this->wrapAtEnd;
    }

    private function replace(string $value): string
    {
        [$from, $to] = [
            \array_keys($this->replacements),
            \array_values($this->replacements),
        ];

        return \str_replace($from, $to, $value);
    }
}
