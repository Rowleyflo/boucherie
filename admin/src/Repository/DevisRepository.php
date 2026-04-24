<?php

namespace App\Repository;

use App\Entity\Devis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Devis>
 */
class DevisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Devis::class);
    }

    /**
     * @return array{items: Devis[], total: int, page: int, perPage: int, totalPages: int}
     */
    public function findFiltered(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $qb = $this->createQueryBuilder('d')
            ->orderBy('d.createdAt', 'DESC');

        if (!empty($filters['statut'])) {
            $qb->andWhere('d.statut = :statut')
               ->setParameter('statut', $filters['statut']);
        }

        if (!empty($filters['prestation'])) {
            $qb->andWhere('d.prestationSlug = :prestation')
               ->setParameter('prestation', $filters['prestation']);
        }

        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $qb->andWhere('d.clientNom LIKE :s OR d.clientPrenom LIKE :s OR d.clientEmail LIKE :s OR d.reference LIKE :s')
               ->setParameter('s', $term);
        }

        if (!empty($filters['date_from'])) {
            $qb->andWhere('d.createdAt >= :date_from')
               ->setParameter('date_from', new \DateTime($filters['date_from'] . ' 00:00:00'));
        }

        if (!empty($filters['date_to'])) {
            $qb->andWhere('d.createdAt <= :date_to')
               ->setParameter('date_to', new \DateTime($filters['date_to'] . ' 23:59:59'));
        }

        $total = (clone $qb)->select('COUNT(d.id)')->getQuery()->getSingleScalarResult();

        $items = $qb
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        return [
            'items'      => $items,
            'total'      => (int) $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function countByStatut(): array
    {
        $rows = $this->createQueryBuilder('d')
            ->select('d.statut, COUNT(d.id) as total')
            ->groupBy('d.statut')
            ->getQuery()
            ->getResult();

        $counts = array_fill_keys(array_keys(Devis::STATUTS), 0);
        foreach ($rows as $row) {
            $counts[$row['statut']] = (int) $row['total'];
        }

        return $counts;
    }

    /**
     * @return array<array{jour: string, total: int}>
     */
    public function countByDay(int $days = 30): array
    {
        $from = new \DateTime("-{$days} days");

        $conn = $this->getEntityManager()->getConnection();
        $rows = $conn->fetchAllAssociative(
            "SELECT DATE_FORMAT(created_at, '%Y-%m-%d') as jour, COUNT(id) as total
             FROM devis
             WHERE created_at >= :from
             GROUP BY jour
             ORDER BY jour ASC",
            ['from' => $from->format('Y-m-d H:i:s')]
        );

        // Fill missing days with 0
        $result = [];
        for ($i = $days; $i >= 0; $i--) {
            $day = (new \DateTime("-{$i} days"))->format('Y-m-d');
            $result[$day] = 0;
        }
        foreach ($rows as $row) {
            $result[$row['jour']] = (int) $row['total'];
        }

        return array_map(fn($jour, $total) => ['jour' => $jour, 'total' => $total], array_keys($result), $result);
    }

    public function findByToken(string $token): ?Devis
    {
        return $this->findOneBy(['tokenAcces' => $token]);
    }

    public function countNouveaux(): int
    {
        return (int) $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.statut = :statut')
            ->setParameter('statut', 'nouveau')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getNextSequenceNumber(): int
    {
        $year = date('Y');
        $count = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.reference LIKE :prefix')
            ->setParameter('prefix', "DEV-{$year}-%")
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count + 1;
    }
}
