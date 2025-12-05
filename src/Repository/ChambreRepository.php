<?php

namespace App\Repository;

use App\Entity\Chambre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Chambre>
 */
class ChambreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chambre::class);
    }
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
    public function findWithCurrentWorks(): array
{
    $now = new \DateTimeImmutable();

    return $this->createQueryBuilder('c')
        ->innerJoin('c.travaux', 't')
        ->addSelect('t')
        ->andWhere('t.isDone = :done')
        ->andWhere('t.startDate <= :now')
        ->andWhere('t.endDate >= :now')
        ->setParameter('done', false)
        ->setParameter('now', $now)
        ->distinct()                      
        ->orderBy('c.id', 'ASC')          
        ->getQuery()
        ->getResult();
}

    //    /**
    //     * @return Chambre[] Returns an array of Chambre objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Chambre
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
