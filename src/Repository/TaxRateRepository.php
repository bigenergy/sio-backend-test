<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TaxRate;
use App\Enum\Country;
use App\Exception\TaxRateNotConfigured;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaxRate>
 */
class TaxRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaxRate::class);
    }

    /**
     * @throws TaxRateNotConfigured
     */
    public function getByCountry(Country $country): TaxRate
    {
        return $this->findOneBy(['country' => $country]) ?? throw TaxRateNotConfigured::forCountry($country);
    }
}
