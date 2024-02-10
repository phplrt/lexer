<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Config;

use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\ReadableInterface;

interface HandlerInterface
{
    public function handle(ReadableInterface $source, TokenInterface $token): ?TokenInterface;
}
