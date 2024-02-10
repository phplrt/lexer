<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Aliases;

final class OrderedGenerator implements GeneratorInterface
{
    /**
     * @var int<0, max>
     */
    private int $id = 0;

    /**
     * @param non-empty-string $prefix
     */
    public function __construct(
        private readonly string $prefix = 'A',
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function generate(array $tokens): string
    {
        do {
            $alias = $this->id($this->id++);

            //
            // The generated alias may conflict with an existing
            // token name. In this case, we skip the index and
            // try to generate another alias.
            //
        } while (isset($tokens[$alias]));

        return $alias;
    }

    /**
     * @return non-empty-string
     */
    private function id(int $id): string
    {
        return $this->prefix . $id;
    }
}
