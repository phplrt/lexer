<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Runtime;

use Phplrt\Lexer\Exception\CompilationException;

/**
 * @internal This is an internal library class, please do not use it in your code.
 * @psalm-internal Phplrt\Lexer\Runtime
 */
class Compiler
{
    /**
     * Default PCRE delimiter.
     *
     * @var non-empty-string
     */
    final public const DEFAULT_DELIMITER = '/';

    /**
     * @link https://www.php.net/manual/en/reference.pcre.pattern.modifiers.php
     *
     * @var list<non-empty-string>
     */
    final public const DEFAULT_MODIFIERS = [
        //
        // - "S": When a pattern is going to be used several times, it is worth
        // spending more time analyzing it in order to speed up the time taken
        // for matching. If this modifier is set, then this extra analysis is
        // performed. At present, studying a pattern is useful only for
        // non-anchored patterns that do not have a single fixed starting
        // character.
        //
        'S',
        //
        // - "s": If this modifier is set, a dot metacharacter in the pattern
        // matches all characters, including newlines. Without it, newlines are
        // excluded. This modifier is equivalent to Perl's /s modifier. A
        // negative class such as [^a] always matches a newline character,
        // independent of the setting of this modifier.
        //
        's',
        //
        // - "u": This modifier turns on additional functionality of PCRE that
        // is incompatible with Perl. Pattern and subject strings are treated as
        // UTF-8. An invalid subject will cause the preg_* function to match
        // nothing; an invalid pattern will trigger an error of level E_WARNING.
        // Five and six octet UTF-8 sequences are regarded as invalid since
        // PHP 5.3.4 (resp. PCRE 7.3 2007-08-28); formerly those have been
        // regarded as valid UTF-8.
        //
        'u',
        //
        // - "m": By default, PCRE treats the subject string as consisting of a
        // single "line" of characters (even if it actually contains several
        // newlines).
        //
        // The "start of line" metacharacter (^) matches only at the start of
        // the string, while the "end of line" metacharacter ($) matches only at
        // the end of the string, or before a terminating newline (unless D
        // modifier is set). This is the same as Perl. When this modifier is
        // set, the "start of line" and "end of line" constructs match
        // immediately following or immediately before any newline in the
        // subject string, respectively, as well as at the very start and end.
        // This is equivalent to Perl's /m modifier. If there are no "\n"
        // characters in a subject string, or no occurrences of ^ or $ in a
        // pattern, setting this modifier has no effect.
        //
        'm',
    ];

    /**
     * @param bool $debug Enables or disables debug mode with validation of
     *        generated code.
     * @param non-empty-string $delimiter Escape character for regular
     *        expressions based on which special characters in token names and
     *        values will be escaped.
     */
    public function __construct(
        private readonly bool $debug,
        private readonly string $delimiter = self::DEFAULT_DELIMITER,
    ) {
    }

    /**
     * @param iterable<non-empty-string, non-empty-string> $tokens List of
     *        PCRE-compatible tokens in format [string(NAME) => string(VALUE)].
     * @param list<non-empty-string> $modifiers List of PCRE modifiers. See
     *        also {@link https://www.php.net/manual/en/reference.pcre.pattern.modifiers.php}
     *
     * @return non-empty-string
     *
     * @throws CompilationException
     */
    public function compile(iterable $tokens, array $modifiers = self::DEFAULT_MODIFIERS): string
    {
        // List of compilable string chunks.
        $chunks = [];

        // Build a collection of "[NAME => PCRE]" tokens to a list
        // from a specific marker "(?:(?:PCRE)(*MARK:NAME))".
        foreach ($tokens as $name => $pcre) {
            if ($name === '') {
                throw CompilationException::fromEmptyTokenName($pcre);
            }

            $chunks[] = $chunk = \vsprintf('(?:(?:%s)(*MARK:%s))', [
                $this->pattern($pcre),
                $this->name($name),
            ]);

            if ($this->debug) {
                $this->assertValidToken($name, $chunk, $modifiers);
            }
        }

        // Finalize compilation to the format:
        //  \G(?|
        //      (?:(?:PCRE_1)(*MARK:NAME_1)) |
        //      (?:(?:PCRE_2)(*MARK:NAME_2)) |
        //  )
        $body = \sprintf('\\G(?|%s)', \implode('|', $chunks));

        if ($this->debug) {
            $this->assertValidResult($body, $modifiers);
        }

        return $this->build($body, $modifiers);
    }

    /**
     * @param non-empty-string|int $name
     * @return non-empty-string
     */
    private function name(string|int $name): string
    {
        /** @var non-empty-string */
        return \preg_quote((string)$name, $this->delimiter);
    }

    /**
     * @param non-empty-string $pattern
     * @return non-empty-string
     */
    private function pattern(string $pattern): string
    {
        /** @var non-empty-string */
        return \addcslashes($pattern, $this->delimiter);
    }

    /**
     * @param non-empty-string|int $name
     * @param non-empty-string $pattern
     * @param list<non-empty-string> $modifiers
     *
     * @throws CompilationException
     */
    private function assertValidToken(string|int $name, string $pattern, array $modifiers): void
    {
        if (\is_int($name)) {
            $name = '#' . $name;
        }

        $this->assertValid($pattern, $modifiers, $name);
    }

    /**
     * @param non-empty-string $pattern
     * @param list<non-empty-string> $modifiers
     *
     * @throws CompilationException
     */
    private function assertValidResult(string $pattern, array $modifiers): void
    {
        $this->assertValid($pattern, $modifiers);
    }

    /**
     * @param non-empty-string $pattern
     * @param list<non-empty-string> $modifiers
     * @param non-empty-string|null $original
     *
     * @throws CompilationException
     */
    private function assertValid(string $pattern, array $modifiers, string $original = null): void
    {
        \error_clear_last();

        $flags = \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE;

        @\preg_match_all($this->build($pattern, $modifiers), '', $matches, $flags);

        if ($error = \error_get_last()) {
            throw new CompilationException(
                $this->formatException($error['message'], $original),
            );
        }
    }

    /**
     * @param non-empty-string $pcre
     * @param list<non-empty-string> $modifiers
     *
     * @return non-empty-string
     */
    private function build(string $pcre, array $modifiers): string
    {
        return $this->delimiter . $pcre . $this->delimiter
            . \implode('', $modifiers);
    }

    /**
     * @return non-empty-string
     */
    private function formatException(string $message, string $token = null): string
    {
        $suffix = \sprintf(' in %s token definition', $token ?: '<unknown>');

        $message = \str_replace('Compilation failed: ', '', $message);
        $message = \preg_replace('/([\w_]+\(\):\h+)/', '', $message);
        $message = \preg_replace('/\h*at\h+offset\h+\d+/', '', $message);

        /** @var non-empty-string */
        return \ucfirst($message) . (\is_string($token) ? $suffix : '');
    }
}
