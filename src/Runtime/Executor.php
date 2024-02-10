<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Runtime;

use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Contracts\Source\SourceExceptionInterface;
use Phplrt\Lexer\Aliases\GeneratorInterface;
use Phplrt\Lexer\Aliases\OrderedGenerator;
use Phplrt\Lexer\Exception\CompilationException;
use Phplrt\Lexer\Lexer;

final class Executor implements ExecutorInterface
{
    /**
     * @var CompiledExecutor
     */
    public readonly CompiledExecutor $compiled;

    /**
     * @var bool
     */
    private readonly bool $debug;

    /**
     * @param array<non-empty-string|int, non-empty-string> $tokens List of
     *        PCRE-compatible tokens in format [NAME_OR_ID => VALUE].
     * @param list<non-empty-string> $skip List of skipped tokens.
     * @param bool|null $debug Enables or disables debug mode. If the parameter
     *        contains {@see null}, then the debug mode depends on the state of
     *        PHP assertions support.
     * @param list<non-empty-string> $modifiers List of PCRE modifiers which
     *        will be applied in the assemblies regular expression.
     * @param non-empty-string $unknown Internal token name for unknown tokens.
     *
     * @throws CompilationException
     */
    public function __construct(
        array $tokens,
        private readonly array $skip,
        bool $composite,
        ?bool $debug = null,
        private readonly array $modifiers = Compiler::DEFAULT_MODIFIERS,
        private readonly string $unknown = Lexer::DEFAULT_UNKNOWN_TOKEN_NAME,
        private readonly GeneratorInterface $aliases = new OrderedGenerator(),
    ) {
        if ($debug === null) {
            // Enable debug in case of assertions is available.
            assert($debug = true);
        }

        $this->debug = $debug;
        $this->compiled = $this->compile($tokens, $composite);
    }

    /**
     * @param array<non-empty-string|int, non-empty-string> $tokens
     *
     * @return array{
     *     0: array<non-empty-string, non-empty-string>,
     *     1: array<non-empty-string, non-empty-string|int>
     * }
     * @throws CompilationException
     */
    private function escape(array $tokens): array
    {
        $result = $aliases = [];

        foreach ($tokens as $id => $pcre) {
            if ($id === '') {
                throw CompilationException::fromEmptyTokenName($pcre);
            }

            if (!self::isValidTokenName($id)) {
                $alias = $this->aliases->generate($tokens);

                $aliases[$alias] = $id;
                $id = $alias;
            }

            /** @var non-empty-string $id */
            $result[$id] = $pcre;
        }

        return [$result, $aliases];
    }

    /**
     * Returns {@see true} in case of token name is allowed
     * for PCRE compilation or {@see false} instead.
     */
    private static function isValidTokenName(string|int $name): bool
    {
        if (\is_int($name) || $name === '') {
            return false;
        }

        // 1) First char MUST not be a number
        // 2) Name MUST contain only A-Z, 0-9 and "_", "-" chars
        return !\is_numeric($name[0])
            && (bool) \preg_match('/^[a-zA-Z0-9_-]+$/', $name);
    }

    /**
     * @param array<non-empty-string|int, non-empty-string> $tokens
     *
     * @throws CompilationException
     */
    private function compile(array $tokens, bool $composite): CompiledExecutor
    {
        $compiler = new Compiler($this->debug);

        // Compile non-ascii token names in a simplified version.
        [$tokens, $aliases] = $this->escape($tokens);

        $tokens[$this->unknown] = '.+?';

        return new CompiledExecutor(
            $compiler->compile($tokens, $this->modifiers),
            $this->skip,
            $this->unknown,
            $aliases,
            $composite,
        );
    }

    /**
     * @throws SourceExceptionInterface
     */
    public function run(ReadableInterface $source, int $offset, ?int $length): iterable
    {
        return $this->compiled->run($source, $offset, $length);
    }
}
