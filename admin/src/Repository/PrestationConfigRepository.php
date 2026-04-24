<?php

namespace App\Repository;

use App\Entity\PrestationConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrestationConfig>
 */
class PrestationConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrestationConfig::class);
    }

    /** @return PrestationConfig[] */
    public function findActives(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.actif = true')
            ->orderBy('p.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return PrestationConfig[] */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.ordre', 'ASC')
            ->addOrderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
