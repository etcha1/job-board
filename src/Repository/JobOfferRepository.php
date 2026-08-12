<?php

namespace App\Repository;

use App\Entity\JobOffer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobOffer>
 */
class JobOfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobOffer::class);
    }

    // Add custom query methods here if needed

    public function findByTitleOrCompany(string $query): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('LOWER(j.title) LIKE :title OR LOWER(j.company) LIKE :company')
            ->setParameter('title', '%' . strtolower($query) . '%')
            ->setParameter('company', '%' . strtolower($query) . '%')
            ->orderBy('j.postedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
