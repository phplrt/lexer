<?php

declare(strict_types=1);

namespace Phplrt\Lexer;

use Phplrt\Contracts\Lexer\Channel;
use Phplrt\Contracts\Lexer\LexerInterface;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Lexer\Config\HandlerInterface;
use Phplrt\Lexer\Config\OnEndOfInput;
use Phplrt\Lexer\Config\OnHiddenToken;
use Phplrt\Lexer\Config\OnUnknownToken;
use Phplrt\Lexer\Exception\CompilationException;
use Phplrt\Lexer\Exception\UnrecognizedTokenException;
use Phplrt\Lexer\Runtime\Executor;
use Phplrt\Lexer\Runtime\ExecutorInterface;
use Phplrt\Lexer\Token\Composite;
use Phplrt\Lexer\Token\EndOfInput;
use Phplrt\Lexer\Token\UnknownToken;

final class Lexer implements LexerInterface
{
    /**
     * Default token name for unidentified tokens.
     *
     * @var non-empty-string
     */
    final public const DEFAULT_UNKNOWN_TOKEN_NAME = UnknownToken::UNKNOWN_NAME;

    /**
     * @var non-empty-string
     */
    final public const DEFAULT_EOI_TOKEN_NAME = EndOfInput::EOI_NAME;

    private ?ExecutorInterface $executor = null;

    /**
     * @param array<non-empty-string|int, non-empty-string> $tokens List of
     *        token names/identifiers and its patterns.
     * @param list<non-empty-string|int> $skip List of hidden token
     *        names/identifiers.
     * @param bool $composite Enables if {@see true} or disables if {@see false}
     *        support for composite tokens ({@see Composite}). Enabling this
     *        option allows to capture PCRE subgroups, but slows down the lexer.
     * @param HandlerInterface $onUnknownToken This setting is responsible for
     *        the behavior of the lexer in case of detection of unrecognized
     *        tokens.
     *
     *        See {@see OnUnknownToken} for more details.
     *
     *        Note that you can also define your own {@see HandlerInterface} to
     *        override behavior.
     * @param HandlerInterface $onHiddenToken This setting is responsible for
     *        the behavior of the lexer in case of detection of hidden/skipped
     *        tokens.
     *
     *        See {@see OnHiddenToken} for more details.
     *
     *        Note that you can also define your own {@see HandlerInterface} to
     *        override behavior.
     * @param HandlerInterface $onEndOfInput This setting is responsible for the
     *        operation of the terminal token ({@see EndOfInput}).
     *
     *        See also {@see OnEndOfInput} for more details.
     *
     *        Note that you can also define your own {@see HandlerInterface} to
     *        override behavior.
     * @param non-empty-string $unknown The identifier that marks each unknown
     *        token inside the executor (internal runtime). This parameter only
     *        needs to be changed if the name is already in use in the user's
     *        token set (in the {@see $tokens} parameter), otherwise it makes
     *        no sense.
     * @param non-empty-string $eoi
     */
    public function __construct(
        private readonly array $tokens,
        private readonly array $skip = [],
        private readonly bool $composite = false,
        private HandlerInterface $onHiddenToken = OnHiddenToken::SKIP,
        private readonly string $unknown = Lexer::DEFAULT_UNKNOWN_TOKEN_NAME,
        private HandlerInterface $onUnknownToken = OnUnknownToken::THROW,
        private readonly string $eoi = Lexer::DEFAULT_EOI_TOKEN_NAME,
        private HandlerInterface $onEndOfInput = OnEndOfInput::RETURN,
    ) {}

    /**
     * @param HandlerInterface $handler A handler that defines the behavior of
     *        the lexer in the case of a "hidden" token.
     *
     * @psalm-immutable This method returns a new {@see LexerInterface} instance
     *                  and does not change the current state of the lexer.
     */
    public function onHiddenToken(HandlerInterface $handler): self
    {
        $self = clone $this;
        $self->onHiddenToken = $handler;

        return $self;
    }

    /**
     * @param HandlerInterface $handler A handler that defines the behavior of
     *        the lexer in the case of an "unknown" token.
     *
     * @psalm-immutable This method returns a new {@see LexerInterface} instance
     *                  and does not change the current state of the lexer.
     */
    public function onUnknownToken(HandlerInterface $handler): self
    {
        $self = clone $this;
        $self->onUnknownToken = $handler;

        return $self;
    }

    /**
     * @param HandlerInterface $handler A handler that defines the behavior of
     *        the lexer in the case of an "end of input" token.
     *
     * @psalm-immutable This method returns a new {@see LexerInterface} instance
     *                  and does not change the current state of the lexer.
     */
    public function onEndOfInput(HandlerInterface $handler): self
    {
        $self = clone $this;
        $self->onEndOfInput = $handler;

        return $self;
    }

    /**
     * @throws CompilationException
     */
    private function warmup(): void
    {
        if ($this->executor === null) {
            $this->executor = new Executor(
                $this->tokens,
                $this->skip,
                $this->composite,
                unknown: $this->unknown,
            );
        }
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     *
     * @throws CompilationException
     * @throws UnrecognizedTokenException
     *
     * @psalm-suppress ImpureMethodCall : The implementation uses memoization,
     *                 which should not change the behavior of the code.
     */
    public function lex(ReadableInterface $source, int $offset = 0, int $length = null): iterable
    {
        assert($offset >= 0, 'Offset value must be non-negative');
        assert($length === null || $length > 0, 'Length value must be greater than 0');

        $this->warmup();

        $unknown = [];
        $token = null;

        /** @psalm-suppress PossiblyNullReference : Executor cannot be null */
        foreach ($this->executor->run($source, $offset, $length) as $token) {
            if ($token->getChannel() === Channel::HIDDEN) {
                if ($result = $this->handleHiddenToken($source, $token)) {
                    yield $result;
                }

                continue;
            }

            if ($token->getName() === $this->unknown) {
                $unknown[] = $token;
                continue;
            }

            if ($unknown !== [] && ($result = $this->handleUnknownToken($source, $unknown))) {
                yield $result;
                $unknown = [];
            }

            yield $token;
        }

        if ($unknown !== [] && $result = $this->handleUnknownToken($source, $unknown)) {
            yield $token = $result;
        }

        if ($eoi = $this->handleEoiToken($source, $token)) {
            yield $eoi;
        }
    }

    /**
     * @param iterable<TokenInterface> $tokens
     * @return array{string, int<0, max>}
     */
    private function merge(iterable $tokens): array
    {
        $offset = null;
        $body = '';

        foreach ($tokens as $token) {
            $offset ??= $token->getOffset();
            $body .= $token->getValue();
        }

        return [$body, $offset ?? 0];
    }

    /**
     * @param non-empty-list<TokenInterface> $tokens
     *
     * @throws UnrecognizedTokenException
     */
    private function handleUnknownToken(ReadableInterface $source, array $tokens): ?TokenInterface
    {
        [$body, $offset] = $this->merge($tokens);

        return $this->onUnknownToken->handle($source, new UnknownToken(
            $body,
            $offset,
            $this->unknown,
        ));
    }

    private function handleEoiToken(ReadableInterface $source, ?TokenInterface $last): ?TokenInterface
    {
        return $this->onEndOfInput->handle($source, new EndOfInput(
            (int) ($last?->getOffset() + $last?->getBytes()),
            $this->eoi,
        ));
    }

    private function handleHiddenToken(ReadableInterface $source, TokenInterface $token): ?TokenInterface
    {
        return $this->onHiddenToken->handle($source, $token);
    }
}
