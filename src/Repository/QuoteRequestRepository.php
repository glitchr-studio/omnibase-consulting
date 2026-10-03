<?php

namespace Base\Consulting\Repository;

use Base\Consulting\Entity\QuoteRequest;
use Base\Consulting\Enum\QuoteStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<QuoteRequest> */
class QuoteRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuoteRequest::class);
    }

    /** @return list<QuoteRequest> the new ones, latest first */
    public function findNew(?int $limit = null): array
    {
        return $this->findBy(['status' => QuoteStatus::NEW], ['createdAt' => 'DESC'], $limit);
    }

    public function countNew(): int
    {
        return $this->count(['status' => QuoteStatus::NEW]);
    }

    /** The requests older than the notice says they are kept: removed, whatever their status. */
    public function purgeOlderThan(\DateTimeImmutable $before): int
    {
        return (int) $this->createQueryBuilder('q')->delete()->where('q.createdAt < :before')->setParameter('before', $before)->getQuery()->execute();
    }
}
