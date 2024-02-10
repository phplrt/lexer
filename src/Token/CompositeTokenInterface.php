<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Token;

use Phplrt\Contracts\Lexer\TokenInterface;

/**
 * @template-extends \Traversable<array-key, TokenInterface>
 * @template-extends \ArrayAccess<array-key, TokenInterface>
 */
interface CompositeTokenInterface extends TokenInterface, \Stringable, \Traversable, \ArrayAccess, \Countable
{
}
