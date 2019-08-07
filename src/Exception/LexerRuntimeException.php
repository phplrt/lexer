<?php
/**
 * This file is part of lexer package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer\Exception;

use Phplrt\Lexer\Token\BaseToken;
use Phplrt\Exception\SourceException;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Contracts\Lexer\Exception\RuntimeExceptionInterface;
use Phplrt\Contracts\Exception\MutableSourceExceptionInterface;
use Phplrt\Contracts\Source\Exception\NotReadableExceptionInterface;

/**
 * Class LexerException
 */
class LexerRuntimeException extends SourceException implements RuntimeExceptionInterface
{
    /**
     * @var TokenInterface|null
     */
    private $token;

    /**
     * @param ReadableInterface $source
     * @param TokenInterface $token
     * @return LexerRuntimeException|$this
     * @throws NotReadableExceptionInterface
     */
    public function onToken(ReadableInterface $source, TokenInterface $token): self
    {
        return $this->withToken($token)->throwsIn($source, $token->getOffset());
    }

    /**
     * @param TokenInterface|null $token
     * @return LexerRuntimeException|$this
     */
    public function withToken(?TokenInterface $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return TokenInterface|BaseToken
     */
    public function getToken(): TokenInterface
    {
        if ($this->token === null) {
            throw new \LogicException(\sprintf('Can not call %s. Token not define', __METHOD__));
        }

        return $this->token;
    }
}
