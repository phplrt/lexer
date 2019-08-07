<?php
/**
 * This file is part of lexer package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Phplrt\Lexer;

use Phplrt\Position\Position;
use Phplrt\Contracts\Lexer\TokenInterface;
use Phplrt\Contracts\Source\FileInterface;
use Phplrt\Contracts\Source\ReadableInterface;
use Phplrt\Contracts\Source\Exception\NotReadableExceptionInterface;

/**
 * Class Renderer
 */
class Printer
{
    /**
     * @var int
     */
    protected const VALUE_CONTENT_LENGTH = 30;

    /**
     * @var string
     */
    private const DEFAULT_FORMAT = '$3offset | $7type | $body ($name) in $src:$line';

    /**
     * @var string
     */
    private const PATTERN = '/\$(.*?)([a-z]+)\b/Ssiu';

    /**
     * @var string
     */
    private $format;

    /**
     * @var LexerInterface
     */
    private $lexer;

    /**
     * Printer constructor.
     *
     * @param LexerInterface $lexer
     * @param string $format
     */
    public function __construct(LexerInterface $lexer, string $format = self::DEFAULT_FORMAT)
    {
        $this->format = $format;
        $this->lexer = $lexer;
    }

    /**
     * @param ReadableInterface $src
     * @param TokenInterface $token
     * @return string
     */
    public function print(TokenInterface $token, ReadableInterface $src): string
    {
        return $this->format(function (string $needle) use ($token, $src): ?string {
            $method = 'render' . \ucfirst($needle);

            if (\method_exists($this, $method)) {
                return (string)$this->$method($token, $src);
            }

            return null;
        });
    }

    /**
     * @param TokenInterface $token
     * @param ReadableInterface $src
     * @return string
     */
    public function printLine(TokenInterface $token, ReadableInterface $src): string
    {
        return $this->print($token, $src) . \PHP_EOL;
    }

    /**
     * @param \Closure $onFound
     * @return string
     */
    private function format(\Closure $onFound): string
    {
        $formattedArguments = [];

        $cb = static function (array $matches) use ($onFound, &$formattedArguments) {
            [, $prefix, $name] = $matches;

            if (($result = $onFound($name)) !== null) {
                $formattedArguments[] = $result;

                return '%' . $prefix . 's';
            }

            return '$' . $prefix . $name;
        };

        $formattedString = \preg_replace_callback(self::PATTERN, $cb, $this->format);

        return \vsprintf($formattedString, $formattedArguments);
    }

    /**
     * @param TokenInterface $_
     * @param ReadableInterface $src
     * @return string
     */
    protected function renderSrc(TokenInterface $_, ReadableInterface $src): string
    {
        if ($src instanceof FileInterface) {
            return $src->getPathName();
        }

        return 'php://input';
    }

    /**
     * @param TokenInterface $token
     * @param ReadableInterface $src
     * @return int
     * @throws NotReadableExceptionInterface
     */
    protected function renderLine(TokenInterface $token, ReadableInterface $src): int
    {
        $position = Position::fromOffset($src, $token->getOffset());

        return $position->getLine();
    }

    /**
     * @param TokenInterface $token
     * @param ReadableInterface $src
     * @return int
     * @throws NotReadableExceptionInterface
     */
    protected function renderColumn(TokenInterface $token, ReadableInterface $src): int
    {
        $position = Position::fromOffset($src, $token->getOffset());

        return $position->getColumn();
    }

    /**
     * @param TokenInterface $token
     * @return int
     */
    protected function renderOffset(TokenInterface $token): int
    {
        return $token->getOffset();
    }

    /**
     * @param TokenInterface $token
     * @return string
     */
    protected function renderId(TokenInterface $token): string
    {
        $hex = \str_pad(\dechex(\abs($token->getType())), 4, '0', \STR_PAD_LEFT);

        return ($token->getType() > 0 ? '' : '-') . ('0x' . $hex);
    }

    /**
     * @param TokenInterface $token
     * @return int
     */
    protected function renderType(TokenInterface $token): int
    {
        return $token->getType();
    }

    /**
     * @param TokenInterface $token
     * @return string
     */
    protected function renderName(TokenInterface $token): string
    {
        return $this->lexer->nameOf($token->getType());
    }

    /**
     * @param TokenInterface $token
     * @return string
     */
    protected function renderValue(TokenInterface $token): string
    {
        return $token->getValue();
    }

    /**
     * @param TokenInterface $token
     * @return int
     */
    protected function renderLength(TokenInterface $token): int
    {
        return \mb_strlen($token->getValue());
    }

    /**
     * @param TokenInterface $token
     * @return int
     */
    protected function renderBytes(TokenInterface $token): int
    {
        return $token->getBytes();
    }

    /**
     * @param TokenInterface $token
     * @return string
     */
    protected function renderBody(TokenInterface $token): string
    {
        $value = $token->getValue();

        $value = (string)(\preg_replace('/\s+/u', ' ', $value) ?? $value);
        $value = \addcslashes($value, '"');

        if (\mb_strlen($value) > static::VALUE_CONTENT_LENGTH + 5) {
            $suffix = \sprintf('… (%s+)', \mb_strlen($value) - static::VALUE_CONTENT_LENGTH);

            $value = \mb_substr($value, 0, static::VALUE_CONTENT_LENGTH) . $suffix;
        }

        return \sprintf('"%s"', \str_replace("\0", '\0', $value));
    }
}
