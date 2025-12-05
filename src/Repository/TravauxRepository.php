<?php

namespace App\Repository;

use App\Entity\Travaux;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Travaux>
 */
class TravauxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Travaux::class);
    }

//    /**
//     * @return Travaux[] Returns an array of Travaux objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Travaux
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findLate(): array
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('t')
            ->andWhere('t.isDone = :done')
            ->andWhere('t.endDate < :now')
            ->setParameter('done', false)
            ->setParameter('now', $now)
            ->orderBy('t.endDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
