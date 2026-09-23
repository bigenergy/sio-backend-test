<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductRepository;
use App\ValueObject\Money;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    /**
     * Stored as cents; Money is what the rest of the application sees.
     */
    #[ORM\Column]
    private int $priceInCents;

    public function __construct(string $name, Money $price)
    {
        $this->name = $name;
        $this->priceInCents = $price->cents();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): Money
    {
        return Money::fromCents($this->priceInCents);
    }
}
