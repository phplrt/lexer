<?php
/**
 * This file is part of lexer package.
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
use Phplrt\Lexer\Exception\LexerException;

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
    private const PATTERN_BODY = self::PATTERN_DELIMITER . '\\G(?|%s)' . self::PATTERN_DELIMITER;

    /**
     * @var string
     */
    private const PATTERN_FLAGS = 'Ssu';

    /**
     * @var string|null
     */
    private $pattern;

    /**
     * @return void
     */
    private function assertPCRECompilation(): void
    {
        if ($error = \error_get_last()) {
            $message = \strpos($error['message'], 'preg_match_all(): Compilation failed: ') === 0
                ? \substr($error['message'], 38)
                : $error['message'];

            throw new LexerException('PCRE Exception: ' . \ucfirst($message));
        }
    }

    /**
     * @param string $source
     * @param int $offset
     * @return \Generator|TokenInterface[]|string
     */
    public function execute(string $source, int $offset): \Generator
    {
        $pattern = $this->getPattern();

        \error_clear_last();

        @\preg_match_all($pattern, $source, $matches, \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE, $offset);

        $this->assertPCRECompilation();

        foreach ($matches as $payload) {
            [$id, $value, $offset] = [(int)$payload['MARK'], $payload[0][0], $payload[0][1]];

            switch (true) {
                case $payload['MARK'] === Unknown::NAME:
                    yield new Unknown($value, $offset);
                    break;

                case \in_array($id, $this->skip, true):
                    yield new Skip($value, $offset);
                    break;

                default:
                    yield new Token($id, $value, $offset);
            }

            if (isset($this->jumps[$id])) {
                return $this->jumps[$id];
            }
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

        $result[] = \vsprintf(self::PATTERN_MARKER, [
            $this->pattern('.+?'),
            $this->name(Unknown::NAME),
        ]);

        return \sprintf(self::PATTERN_BODY, \implode('|', $result)) . self::PATTERN_FLAGS;
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
