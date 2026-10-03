<?php

namespace Base\Consulting\Repository;

use Base\Consulting\Entity\Offering;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Offering> */
class OfferingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offering::class);
    }

    /** @return list<Offering> the visible ones, in their order */
    public function findVisible(?int $limit = null): array
    {
        return $this->findBy(['visible' => true], ['position' => 'ASC', 'id' => 'ASC'], $limit);
    }

    public function findOneVisible(string $slug): ?Offering
    {
        return $this->findOneBy(['slug' => $slug, 'visible' => true]);
    }
}
