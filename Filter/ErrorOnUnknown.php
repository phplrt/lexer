<?php
/**
 * This file is part of lexer package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer\Filter;

use Phplrt\Lexer\Token\Unknown;
use Phplrt\Lexer\LexerInterface;
use Phplrt\Lexer\Token\BaseToken;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Lexer\Exception\UnrecognizedTokenException;
use Phplrt\Contracts\Lexer\Exception\LexerExceptionInterface;
use Phplrt\Contracts\Lexer\Exception\RuntimeExceptionInterface;
use Phplrt\Contracts\Source\Exception\NotReadableExceptionInterface;

/**
 * Class ErrorOnUnknown
 */
class ErrorOnUnknown implements LexerInterface
{
    /**
     * @var string
     */
    private const ERROR_UNKNOWN_TOKEN = 'Unrecognized token %s';

    /**
     * @var LexerInterface
     */
    private $lexer;

    /**
     * IgnoreSkips constructor.
     *
     * @param LexerInterface $lexer
     */
    public function __construct(LexerInterface $lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * @param ReadableInterface $source
     * @return iterable|TokenInterface[]
     * @throws LexerExceptionInterface
     * @throws RuntimeExceptionInterface
     * @throws NotReadableExceptionInterface
     */
    public function lex(ReadableInterface $source): iterable
    {
        /** @var BaseToken $token */
        foreach ($this->lexer->lex($source) as $token) {
            if ($token->getType() === Unknown::ID) {
                throw $this->unrecognizedTokenError($source, $token);
            }

            yield $token;
        }
    }

    /**
     * @param ReadableInterface $src
     * @param BaseToken $token
     * @return UnrecognizedTokenException
     * @throws NotReadableExceptionInterface
     */
    private function unrecognizedTokenError(ReadableInterface $src, BaseToken $token): UnrecognizedTokenException
    {
        $name = $token->render($this->nameOf($token->getType()));

        $exception = new UnrecognizedTokenException(\sprintf(self::ERROR_UNKNOWN_TOKEN, $name));
        $exception->onToken($src, $token);

        return $exception;
    }

    /**
     * @param int $token
     * @return string
     */
    public function nameOf(int $token): string
    {
        return $this->lexer->nameOf($token);
    }
}
