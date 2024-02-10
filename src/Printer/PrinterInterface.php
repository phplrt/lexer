<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Printer;

use Phplrt\Contracts\Lexer\TokenInterface;

interface PrinterInterface
{
    /**
     * @return non-empty-string
     */
    public function printToken(TokenInterface $token): string;
}
