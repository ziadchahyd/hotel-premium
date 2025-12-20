<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ChambreRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reservation')]
#[IsGranted('ROLE_CLIENT')]
class ReservationController extends AbstractController
{
    #[Route(name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $client = $this->getUser();
        $reservations = $reservationRepository->findByClient($client);

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/api/chambres-disponibles', name: 'app_api_chambres_disponibles', methods: ['GET'])]
    #[IsGranted('ROLE_CLIENT')]
    public function getChambresDisponibles(
        Request $request,
        ChambreRepository $chambreRepository,
        ReservationRepository $reservationRepository
    ): JsonResponse {
        $hotelId = $request->query->get('hotel');
        $checkIn = $request->query->get('checkIn');
        $checkOut = $request->query->get('checkOut');

        if (!$hotelId || !$checkIn || !$checkOut) {
            return $this->json(['chambres' => [], 'error' => 'Paramètres manquants'], 400);
        }

        try {
            $checkInDate = new \DateTimeImmutable($checkIn);
            $checkOutDate = new \DateTimeImmutable($checkOut);
        } catch (\Exception $e) {
            return $this->json(['chambres' => [], 'error' => 'Format de date invalide'], 400);
        }

        // Vérifier que la date de départ est après la date d'arrivée
        if ($checkOutDate <= $checkInDate) {
            return $this->json(['chambres' => [], 'error' => 'La date de départ doit être après la date d\'arrivée'], 400);
        }

        // Récupérer toutes les chambres de l'hôtel
        $chambres = $chambreRepository->findBy([
            'hotel' => $hotelId
        ]);

        // Si aucune chambre trouvée pour cet hôtel
        if (empty($chambres)) {
            return $this->json(['chambres' => [], 'error' => 'Aucune chambre trouvée pour cet hôtel'], 200);
        }

        $availableChambres = [];
        foreach ($chambres as $chambre) {
            // Vérifier que la chambre est disponible (isAvailable = true)
            if (!$chambre->isAvailable()) {
                continue;
            }

            // Vérifier si la chambre est en travaux pendant ces dates
            $isInTravaux = false;
            foreach ($chambre->getTravaux() as $travaux) {
                $travauxStart = $travaux->getStartDate(); // CORRECTION : getStartDate() au lieu de getDateDebut()
                $travauxEnd = $travaux->getEndDate(); // CORRECTION : getEndDate() au lieu de getDateFin()

                if ($travauxStart && $travauxEnd) {
                    // Vérifier si les dates se chevauchent
                    // Ne considérer que les travaux non terminés (isDone = false)
                    if (!$travaux->isDone() && !($checkOutDate <= $travauxStart || $checkInDate >= $travauxEnd)) {
                        $isInTravaux = true;
                        break;
                    }
                }
            }

            if ($isInTravaux) {
                continue;
            }

            // Vérifier si la chambre est déjà réservée pendant ces dates
            if ($reservationRepository->isChambreAvailable($chambre, $checkInDate, $checkOutDate)) {
                $availableChambres[] = [
                    'id' => $chambre->getId(),
                    'number' => $chambre->getNumber(),
                    'pricePerNight' => $chambre->getPricePerNight(),
                    'label' => sprintf('Chambre %d - %.2f€/nuit', $chambre->getNumber(), $chambre->getPricePerNight())
                ];
            }
        }

        return $this->json([
            'chambres' => $availableChambres,
            'debug' => [
                'total_chambres' => count($chambres),
                'chambres_disponibles' => count($availableChambres),
                'hotel_id' => $hotelId
            ]
        ]);
    }

    #[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        ChambreRepository $chambreRepository
    ): Response {
        $reservation = new Reservation();
        $reservation->setClient($this->getUser());
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chambre = $reservation->getChambre();
            $checkIn = $reservation->getCheckIn();
            $checkOut = $reservation->getCheckOut();

            // Vérifier que la chambre est disponible
            if (!$chambre->isAvailable()) {
                $this->addFlash('error', 'Cette chambre n\'est pas disponible.');
                return $this->render('reservation/new.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form,
                ]);
            }

            // Vérifier si la chambre est en travaux pendant ces dates
            $isInTravaux = false;
            foreach ($chambre->getTravaux() as $travaux) {
                $travauxStart = $travaux->getStartDate(); // CORRECTION
                $travauxEnd = $travaux->getEndDate(); // CORRECTION

                if ($travauxStart && $travauxEnd) {
                    // Ne considérer que les travaux non terminés
                    if (!$travaux->isDone() && !($checkOut <= $travauxStart || $checkIn >= $travauxEnd)) {
                        $isInTravaux = true;
                        break;
                    }
                }
            }

            if ($isInTravaux) {
                $this->addFlash('error', 'Cette chambre est en travaux pendant les dates sélectionnées.');
                return $this->render('reservation/new.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form,
                ]);
            }

            // Vérifier la disponibilité via le repository
            if (!$reservationRepository->isChambreAvailable($chambre, $checkIn, $checkOut)) {
                $this->addFlash('error', 'Cette chambre n\'est pas disponible pour les dates sélectionnées (déjà réservée).');
                return $this->render('reservation/new.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form,
                ]);
            }

            // Calculer le prix total
            $nights = $reservation->getNumberOfNights();
            $reservation->setTotalPrice($nights * $chambre->getPricePerNight());
            $reservation->setStatus('pending');

            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation créée avec succès !');
            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        // Vérifier que la réservation appartient au client connecté
        if ($reservation->getClient() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository
    ): Response {
        // Vérifier que la réservation appartient au client connecté
        if ($reservation->getClient() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chambre = $reservation->getChambre();
            $checkIn = $reservation->getCheckIn();
            $checkOut = $reservation->getCheckOut();

            // Vérifier que la chambre est disponible
            if (!$chambre->isAvailable()) {
                $this->addFlash('error', 'Cette chambre n\'est pas disponible.');
                return $this->render('reservation/edit.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form,
                ]);
            }

            // Vérifier si la chambre est en travaux pendant ces dates
            $isInTravaux = false;
            foreach ($chambre->getTravaux() as $travaux) {
                $travauxStart = $travaux->getStartDate(); // CORRECTION
                $travauxEnd = $travaux->getEndDate(); // CORRECTION

                if ($travauxStart && $travauxEnd) {
                    if (!$travaux->isDone() && !($checkOut <= $travauxStart || $checkIn >= $travauxEnd)) {
                        $isInTravaux = true;
                        break;
                    }
                }
            }

            if ($isInTravaux) {
                $this->addFlash('error', 'Cette chambre est en travaux pendant les dates sélectionnées.');
                return $this->render('reservation/edit.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form,
                ]);
            }

            // Vérifier la disponibilité (en excluant la réservation actuelle)
            $conflictingReservations = $reservationRepository->createQueryBuilder('r')
                ->where('r.chambre = :chambre')
                ->andWhere('r.id != :currentId')
                ->andWhere('r.status != :cancelled')
                ->andWhere('NOT (r.checkOut <= :checkIn OR r.checkIn >= :checkOut)')
                ->setParameter('chambre', $chambre)
                ->setParameter('currentId', $reservation->getId())
                ->setParameter('cancelled', 'cancelled')
                ->setParameter('checkIn', $checkIn)
                ->setParameter('checkOut', $checkOut)
                ->getQuery()
                ->getResult();

            if (count($conflictingReservations) > 0) {
                $this->addFlash('error', 'Cette chambre n\'est pas disponible pour les dates sélectionnées.');
                return $this->render('reservation/edit.html.twig', [
                    'reservation' => $reservation,
                    'form' => $form,
                ]);
            }

            // Recalculer le prix total
            $nights = $reservation->getNumberOfNights();
            $reservation->setTotalPrice($nights * $chambre->getPricePerNight());

            $entityManager->flush();

            $this->addFlash('success', 'Réservation modifiée avec succès !');
            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $entityManager
    ): Response {
        // Vérifier que la réservation appartient au client connecté
        if ($reservation->getClient() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
            $this->addFlash('success', 'Réservation supprimée avec succès !');
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/cancel', name: 'app_reservation_cancel', methods: ['POST'])]
    public function cancel(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $entityManager
    ): Response {
        // Vérifier que la réservation appartient au client connecté
        if ($reservation->getClient() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('cancel'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            $reservation->setStatus('cancelled');
            $entityManager->flush();
            $this->addFlash('success', 'Réservation annulée avec succès !');
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}