<?php

declare(strict_types=1);

namespace Phplrt\Lexer\Printer;

/**
 * @psalm-consistent-constructor
 */
abstract class Printer implements PrinterInterface
{
    protected static ?self $instance = null;

    abstract public function __construct();

    public static function getInstance(): self
    {
        return self::$instance ??= new static();
    }

    public static function setInstance(?self $instance = null): void
    {
        self::$instance = $instance;
    }
}
