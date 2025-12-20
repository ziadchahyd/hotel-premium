<?php

namespace App\Repository;

use App\Entity\Chambre;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Vérifie si une chambre est disponible pour les dates données
     */
    public function isChambreAvailable(Chambre $chambre, \DateTimeImmutable $checkIn, \DateTimeImmutable $checkOut): bool
    {
        // Vérifier s'il existe des réservations qui se chevauchent avec les dates demandées
        $conflictingReservations = $this->createQueryBuilder('r')
            ->where('r.chambre = :chambre')
            ->andWhere('r.status != :cancelled')
            ->andWhere('NOT (r.checkOut <= :checkIn OR r.checkIn >= :checkOut)')
            ->setParameter('chambre', $chambre)
            ->setParameter('cancelled', 'cancelled')
            ->setParameter('checkIn', $checkIn)
            ->setParameter('checkOut', $checkOut)
            ->getQuery()
            ->getResult();

        return count($conflictingReservations) === 0;
    }

    /**
     * Trouve les réservations d'un client
     */
    public function findByClient($client): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.client = :client')
            ->setParameter('client', $client)
            ->orderBy('r.checkIn', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les chambres actuellement occupées
     */
    public function getCurrentlyOccupiedChambres(): array
    {
        $today = new \DateTimeImmutable();

        return $this->createQueryBuilder('r')
            ->where('r.checkIn <= :today')
            ->andWhere('r.checkOut > :today')
            ->andWhere('r.status != :cancelled')
            ->setParameter('today', $today)
            ->setParameter('cancelled', 'cancelled')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les réservations à venir
     */
    public function getUpcomingReservations(): array
    {
        $today = new \DateTimeImmutable();

        return $this->createQueryBuilder('r')
            ->where('r.checkIn > :today')
            ->andWhere('r.status != :cancelled')
            ->setParameter('today', $today)
            ->setParameter('cancelled', 'cancelled')
            ->orderBy('r.checkIn', 'ASC')
            ->getQuery()
            ->getResult();
    }
}