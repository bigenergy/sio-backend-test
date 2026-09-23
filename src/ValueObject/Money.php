<?php

declare(strict_types=1);

namespace App\ValueObject;

/**
 * An amount of money, held as whole cents so the arithmetic never leaves
 * integers. Every operation returns a new instance.
 */
final readonly class Money implements \Stringable
{
    public const CURRENCY = 'EUR';

    private function __construct(private int $cents)
    {
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function cents(): int
    {
        return $this->cents;
    }

    public function plus(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function minus(self $other): self
    {
        return new self($this->cents - $other->cents);
    }

    /**
     * The given share of this amount, rounded to the nearest cent. Accepts a
     * fractional percent, so 7.5% is expressible.
     */
    public function percentage(float $percent): self
    {
        return new self((int) round($this->cents * $percent / 100));
    }

    public function atMost(self $ceiling): self
    {
        return $this->cents <= $ceiling->cents ? $this : $ceiling;
    }

    public function equals(self $other): bool
    {
        return $this->cents === $other->cents;
    }

    public function toEuros(): float
    {
        return round($this->cents / 100, 2);
    }

    public function __toString(): string
    {
        return number_format($this->cents / 100, 2, '.', '');
    }
}
