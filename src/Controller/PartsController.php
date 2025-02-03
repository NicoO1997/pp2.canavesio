<?php
// src/Controller/PartsController.php

namespace App\Controller;

use App\Entity\Parts;
use App\Form\PartsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PartsController extends AbstractController
{
    #[Route('/parts/new', name: 'parts_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $part = new Parts();
        $form = $this->createForm(PartsType::class, $part);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($part);
            $entityManager->flush();

            return $this->redirectToRoute('parts_success');
        }

        return $this->render('parts/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/parts/success', name: 'parts_success')]
    public function success(): Response
    {
        return new Response('Part added successfully!');
    }
}
