<?php

namespace App\Controller;

use App\Repository\ChambreRepository;
use App\Repository\HotelRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/login', name: 'admin_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_admin');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/admin/occupation', name: 'app_admin_occupation')]
    #[IsGranted('ROLE_ADMIN')]
    public function occupation(
        HotelRepository $hotelRepository, 
        ChambreRepository $chambreRepository,
        ReservationRepository $reservationRepository
    ): Response {
        $hotels = $hotelRepository->findAll();
        $occupationData = [];
        $today = new \DateTimeImmutable();

        foreach ($hotels as $hotel) {
            $chambres = $chambreRepository->findBy(['hotel' => $hotel]);
            $totalChambres = count($chambres);
            $occupiedChambres = 0;

            foreach ($chambres as $chambre) {
                // Occupation en temps réel : chambres occupées AUJOURD'HUI
                $reservations = $reservationRepository->createQueryBuilder('r')
                    ->where('r.chambre = :chambre')
                    ->andWhere('r.status != :cancelled')
                    ->andWhere('r.checkIn <= :today')
                    ->andWhere('r.checkOut > :today')
                    ->setParameter('chambre', $chambre)
                    ->setParameter('cancelled', 'cancelled')
                    ->setParameter('today', $today)
                    ->getQuery()
                    ->getResult();

                if (count($reservations) > 0) {
                    $occupiedChambres++;
                }
            }

            $occupationData[] = [
                'hotel' => $hotel,
                'totalChambres' => $totalChambres,
                'occupiedChambres' => $occupiedChambres,
                'occupationRate' => $totalChambres > 0 ? ($occupiedChambres / $totalChambres) * 100 : 0,
            ];
        }

        return $this->render('admin/occupation.html.twig', [
            'occupationData' => $occupationData,
        ]);
    }

    #[Route('/admin/occupation/range', name: 'app_admin_occupation_range', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function occupationRange(
        Request $request,
        HotelRepository $hotelRepository,
        ChambreRepository $chambreRepository,
        ReservationRepository $reservationRepository
    ): Response {
        $today = new \DateTimeImmutable();
        $defaultEndDate = $today->modify('+30 days');

        // Récupérer les dates depuis le formulaire POST
        $startDateStr = $request->request->get('startDate');
        $endDateStr = $request->request->get('endDate');

        // Si pas de POST, essayer GET (pour les liens)
        if (!$startDateStr) {
            $startDateStr = $request->query->get('startDate');
        }
        if (!$endDateStr) {
            $endDateStr = $request->query->get('endDate');
        }

        // Parser les dates
        try {
            if ($startDateStr) {
                $startDate = new \DateTimeImmutable($startDateStr);
            } else {
                $startDate = $today;
            }

            if ($endDateStr) {
                $endDate = new \DateTimeImmutable($endDateStr);
            } else {
                $endDate = $defaultEndDate;
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Format de date invalide. Utilisation des dates par défaut.');
            $startDate = $today;
            $endDate = $defaultEndDate;
        }

        // Vérifier que la date de fin est après la date de début
        if ($endDate <= $startDate) {
            $this->addFlash('error', 'La date de fin doit être après la date de début.');
            $endDate = $startDate->modify('+30 days');
        }

        $hotels = $hotelRepository->findAll();
        $occupationData = [];

        foreach ($hotels as $hotel) {
            $chambres = $chambreRepository->findBy(['hotel' => $hotel]);
            $totalChambres = count($chambres);
            $occupiedChambres = 0;
            $occupiedChambresList = [];

            foreach ($chambres as $chambre) {
                // Rechercher les réservations qui se chevauchent avec la plage de dates
                // Une réservation chevauche si :
                // - Elle commence avant la fin de la plage ET
                // - Elle se termine après le début de la plage
                $overlappingReservations = $reservationRepository->createQueryBuilder('r')
                    ->where('r.chambre = :chambre')
                    ->andWhere('r.status != :cancelled')
                    ->andWhere('r.checkIn < :endDate')
                    ->andWhere('r.checkOut > :startDate')
                    ->setParameter('chambre', $chambre)
                    ->setParameter('cancelled', 'cancelled')
                    ->setParameter('startDate', $startDate)
                    ->setParameter('endDate', $endDate)
                    ->getQuery()
                    ->getResult();

                if (count($overlappingReservations) > 0) {
                    $occupiedChambres++;
                    // Stocker les détails des réservations pour affichage
                    $occupiedChambresList[] = [
                        'chambre' => $chambre,
                        'reservations' => $overlappingReservations
                    ];
                }
            }

            $occupationData[] = [
                'hotel' => $hotel,
                'totalChambres' => $totalChambres,
                'occupiedChambres' => $occupiedChambres,
                'occupationRate' => $totalChambres > 0 ? ($occupiedChambres / $totalChambres) * 100 : 0,
                'occupiedChambresList' => $occupiedChambresList, // Pour afficher les détails
            ];
        }

        return $this->render('admin/occupation_range.html.twig', [
            'occupationData' => $occupationData,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    #[Route('/admin/logout', name: 'admin_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}