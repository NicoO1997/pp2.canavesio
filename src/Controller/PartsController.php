<?php

namespace App\Controller;

use App\Entity\Parts;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PartsController extends AbstractController
{
    #[Route('/parts/add', name: 'app_add_part')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $part = new Parts();
            $part->setName($request->request->get('name'));
            $part->setBrand($request->request->get('brand'));
            $part->setQuantity((int)$request->request->get('quantity'));

            $entityManager->persist($part);
            $entityManager->flush();

            $this->addFlash('success', 'Repuesto añadido correctamente');
            return $this->redirectToRoute('app_ver_stock');
        }

        return $this->render('parts/add.html.twig');
    }

    #[Route('/parts/edit/{id}', name: 'app_editar_repuesto')]
    public function edit(Request $request, EntityManagerInterface $entityManager, Parts $part): Response
    {
        if ($request->isMethod('POST')) {
            $part->setName($request->request->get('name'));
            $part->setBrand($request->request->get('brand'));
            $part->setQuantity((int)$request->request->get('quantity'));

            $entityManager->flush();
            $this->addFlash('success', 'Repuesto actualizado correctamente');
            return $this->redirectToRoute('app_ver_stock');
        }

        return $this->render('parts/edit.html.twig', [
            'part' => $part
        ]);
    }

    #[Route('/parts/delete/{id}', name: 'app_eliminar_repuesto')]
    public function delete(EntityManagerInterface $entityManager, Parts $part): Response
    {
        $entityManager->remove($part);
        $entityManager->flush();

        $this->addFlash('success', 'Repuesto eliminado correctamente');
        return $this->redirectToRoute('app_ver_stock');
    }
}