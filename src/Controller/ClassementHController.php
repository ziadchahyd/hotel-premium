<?php

namespace App\Controller;

use App\Entity\ClassementH;
use App\Form\ClassementHType;
use App\Repository\ClassementHRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/classement/h')]
final class ClassementHController extends AbstractController
{
    #[Route(name: 'app_classement_h_index', methods: ['GET'])]
    public function index(ClassementHRepository $classementHRepository): Response
    {
        return $this->render('classement_h/index.html.twig', [
            'classement_hs' => $classementHRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_classement_h_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $classementH = new ClassementH();
        $form = $this->createForm(ClassementHType::class, $classementH);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($classementH);
            $entityManager->flush();

            return $this->redirectToRoute('app_classement_h_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('classement_h/new.html.twig', [
            'classement_h' => $classementH,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_classement_h_show', methods: ['GET'])]
    public function show(ClassementH $classementH): Response
    {
        return $this->render('classement_h/show.html.twig', [
            'classement_h' => $classementH,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_classement_h_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ClassementH $classementH, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClassementHType::class, $classementH);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_classement_h_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('classement_h/edit.html.twig', [
            'classement_h' => $classementH,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_classement_h_delete', methods: ['POST'])]
    public function delete(Request $request, ClassementH $classementH, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$classementH->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($classementH);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_classement_h_index', [], Response::HTTP_SEE_OTHER);
    }
}
