<?php
/**
 * This file is part of phplrt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer\State;

use Phplrt\Lexer\Token\Skip;
use Phplrt\Lexer\Token\Token;
use Phplrt\Lexer\Token\Unknown;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Lexer\Exception\CompilationException;

/**
 * Class Markers
 */
class Markers extends State
{
    /**
     * @var string
     */
    private const PATTERN_DELIMITER = '/';

    /**
     * @var string
     */
    private const PATTERN_MARKER = '(?:%s)(*MARK:%s)';

    /**
     * @var string
     */
    private const PATTERN_BODY = '/\\G(?|%s)/Ssu';

    /**
     * @var string
     */
    private const ERROR_PCRE_ORIGINAL_PREFIX = 'preg_match_all(): Compilation failed: ';

    /**
     * @var string
     */
    private const MARKER = 'MARK';

    /**
     * @var array|string[]
     */
    protected $tokens;

    /**
     * @param string $source
     * @param int $offset
     * @return \Generator|TokenInterface[]|string
     */
    public function execute(string $source, int $offset): \Generator
    {
        foreach ($this->match($this->getPattern(), $source, $offset) as $payload) {
            [$id, $value, $offset] = [(int)$payload[self::MARKER], $payload[0][0], $payload[0][1]];

            if (isset($this->before[$id])) {
                return $this->before[$id];
            }

            switch (true) {
                case $payload[self::MARKER] === Unknown::NAME:
                    yield new Unknown($value, $offset);
                    break;

                case \in_array($id, $this->skip, true):
                    yield new Skip($value, $offset);
                    break;

                default:
                    yield new Token($id, $value, $offset);
            }

            if (isset($this->after[$id])) {
                return $this->after[$id];
            }
        }
    }

    /**
     * @param string $pattern
     * @param string $source
     * @param int $offset
     * @return array
     */
    private function match(string $pattern, string $source, int $offset): array
    {
        \error_clear_last();

        @\preg_match_all($pattern, $source, $matches, \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE, $offset);

        $this->assertPCRECompilation();

        return $matches;
    }

    /**
     * @return void
     */
    private function assertPCRECompilation(): void
    {
        if ($error = \error_get_last()) {
            $message = \strpos($error['message'], self::ERROR_PCRE_ORIGINAL_PREFIX) === 0
                ? \substr($error['message'], \strlen(self::ERROR_PCRE_ORIGINAL_PREFIX))
                : $error['message'];

            throw new CompilationException(\ucfirst($message));
        }
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern ?? $this->pattern = $this->compile();
    }

    /**
     * @return string
     */
    private function compile(): string
    {
        $result = [];

        foreach ($this->tokens as $name => $pattern) {
            $result[] = \sprintf(self::PATTERN_MARKER, $this->pattern($pattern), $this->name($name));
        }

        $result[] = $this->unknown();

        return \sprintf(self::PATTERN_BODY, \implode('|', $result));
    }

    /**
     * @return string
     */
    private function unknown(): string
    {
        return \vsprintf(self::PATTERN_MARKER, [
            $this->pattern('.+?'),
            $this->name(Unknown::NAME),
        ]);
    }

    /**
     * @param string $pattern
     * @return string
     */
    private function pattern(string $pattern): string
    {
        return \addcslashes($pattern, self::PATTERN_DELIMITER);
    }

    /**
     * @param string|int $name
     * @return string
     */
    private function name($name): string
    {
        return \preg_quote((string)$name, self::PATTERN_DELIMITER);
    }
}
