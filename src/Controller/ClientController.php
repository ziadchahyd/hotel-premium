<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/client')]
#[IsGranted('ROLE_CLIENT')]
final class ClientController extends AbstractController
{
    #[Route('/dashboard', name: 'app_client_dashboard', methods: ['GET'])]
    public function dashboard(ReservationRepository $reservationRepository): Response
    {
        $client = $this->getUser();
        $reservations = $reservationRepository->findByClient($client);

        return $this->render('client/dashboard.html.twig', [
            'client' => $client,
            'reservations' => $reservations,
        ]);
    }
}