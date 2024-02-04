<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Tests\Unit;

use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Lexer\Compiler\CompilerInterface;
use Phplrt\Lexer\Driver\DriverInterface;
use Phplrt\Lexer\Exception\LexerExceptionInterface;
use Phplrt\Lexer\MutableLexerInterface;
use Phplrt\Lexer\Token\CompositeTokenInterface;
use PHPUnit\Framework\Attributes\Group;

/**
 * Note: Changing the behavior of these tests is allowed ONLY when updating
 *       a MAJOR version of the package.
 */
#[Group('phplrt/lexer'), Group('unit')]
class CompatibilityTest extends TestCase
{
    public function testMutableLexerCompatibility(): void
    {
        self::expectNotToPerformAssertions();

        new class implements MutableLexerInterface {
            public function append(string $token, string $pattern): MutableLexerInterface {}
            public function appendMany(array $tokens): MutableLexerInterface {}
            public function prepend(string $token, string $pattern): MutableLexerInterface {}
            public function prependMany(array $tokens, bool $reverseOrder = true): MutableLexerInterface {}
            public function skip(string ...$tokens): MutableLexerInterface {}
            public function remove(string ...$tokens): MutableLexerInterface {}
        };
    }

    public function testCompositeTokenCompatibility(): void
    {
        self::expectNotToPerformAssertions();

        new class implements CompositeTokenInterface {
            public function getIterator(): \Traversable {}

            public function offsetExists(mixed $offset): bool {}
            public function offsetGet(mixed $offset): mixed {}
            public function offsetSet(mixed $offset, mixed $value): void {}
            public function offsetUnset(mixed $offset): void {}

            public function count(): int {}
            public function getName(): string {}
            public function getOffset(): int {}
            public function getValue(): string {}
            public function getBytes(): int {}
        };
    }

    public function testLexerExceptionCompatibility(): void
    {
        self::expectNotToPerformAssertions();

        new class extends \Exception implements LexerExceptionInterface {};
    }

    public function testDriverCompatibility(): void
    {
        self::expectNotToPerformAssertions();

        new class implements DriverInterface {
            public function run(array $tokens, ReadableInterface $source, int $offset = 0): iterable {}
            public function reset(): void {}
        };
    }

    public function testCompilerCompatibility(): void
    {
        self::expectNotToPerformAssertions();

        new class implements CompilerInterface {
            public function compile(array $tokens): string {}
        };
    }
}
