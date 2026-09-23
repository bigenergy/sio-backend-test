<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Country;
use App\Repository\TaxRateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * The VAT rate charged for one country.
 *
 * Kept in the database rather than in code because rates change by decree and
 * a shop should not need a release to follow them. Decimal, since reduced and
 * fractional rates exist across the EU.
 */
#[ORM\Entity(repositoryClass: TaxRateRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_tax_rate_country', columns: ['country'])]
class TaxRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 2, enumType: Country::class)]
    private Country $country;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private string $percent;

    public function __construct(Country $country, float $percent)
    {
        $this->country = $country;
        $this->percent = (string) $percent;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function getPercent(): float
    {
        return (float) $this->percent;
    }
}
