<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use App\Exception\ProductNotFound;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @throws ProductNotFound
     */
    public function getById(int $id): Product
    {
        return $this->find($id) ?? throw ProductNotFound::withId($id);
    }
}
