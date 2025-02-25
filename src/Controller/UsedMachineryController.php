<?php

namespace App\Controller;

use App\Entity\UsedMachinery;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UsedMachineryType;
use App\Repository\UserFavoriteProductRepository;

class UsedMachineryController extends AbstractController
{
    #[Route('/view-used-machinery', name: 'app_view_used_machinery')]
    public function viewUsedMachinery(EntityManagerInterface $entityManager): Response
    {
        $usedMachineries = $entityManager->getRepository(UsedMachinery::class)->findAll();

        return $this->render('used_machinery/view.html.twig', [
            'usedMachineries' => $usedMachineries,
        ]);
    }

    #[Route('/add-used-machinery', name: 'app_add_used_machinery_page', methods: ['GET', 'POST'])]
    public function showAddUsedMachineryForm(Request $request, EntityManagerInterface $entityManager): Response
    {
        $machinery = new UsedMachinery();
        $machinery->setLastService(new \DateTime());
        
        $form = $this->createForm(UsedMachineryType::class, $machinery);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $isNew = $form->get('isNew')->getData();

                if ($isNew) {
                    $machinery->setHoursOfUse(0);
                    $machinery->setMonths(0);
                    $machinery->setYearsOld(0);
                    $machinery->setLastService(new \DateTime());
                }

                $imageFile = $form->get('imageFilename')->getData();
                
                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = preg_replace('/[^A-Za-z0-9]/', '-', $originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                    
                    try {
                        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/imagenes';
                        
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        
                        $imageFile->move(
                            $uploadDir,
                            $newFilename
                        );
                        
                        $machinery->setImageFilename($newFilename);
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Error al subir la imagen: ' . $e->getMessage());
                        $machinery->setImageFilename('default.jpg');
                    }
                } else {
                    $machinery->setImageFilename('default.jpg');
                }

                $entityManager->persist($machinery);
                $entityManager->flush();

                $this->addFlash('success', 'Maquinaria agregada con éxito.');
                return $this->redirectToRoute('app_view_used_machinery');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al guardar la maquinaria: ' . $e->getMessage());
            }
        } else if ($form->isSubmitted()) {
            $this->addFlash('error', 'Error en el formulario. Por favor revisa los campos.');
        }

        return $this->render('used_machinery/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/usedMachinery/category/{category}', name: 'used_machinery_category')]
    public function category(
        string $category,
        EntityManagerInterface $entityManager,
        UserFavoriteProductRepository $userFavoriteProductRepository
    ): Response {
        $user = $this->getUser();
        $usedMachineries = $entityManager->getRepository(UsedMachinery::class)->findBy(['category' => $category]);

        $favoriteProductIds = [];
        if ($user) {
            $favorites = $userFavoriteProductRepository->findBy(['user' => $user]);
            $favoriteProductIds = array_map(function ($favorite) {
                return $favorite->getProduct()->getId();
            }, $favorites);
        }

        return $this->render('used_machinery/view.html.twig', [
            'usedMachineries' => $usedMachineries,
            'favoriteProductIds' => $favoriteProductIds,
        ]);
    }

    #[Route('/usedMachinery/section/{section}', name: 'used_machinery_section')]
    public function section(
        string $section,
        EntityManagerInterface $entityManager
    ): Response {
        $categoryMap = [
            'Tractores' => 'tractor',
            'Embutidoras' => 'embutidora',
            'Sembradoras' => 'sembradora',
            'Equipos de Forraje' => 'equipo de forraje',
        ];

        $category = $categoryMap[$section] ?? null;

        if (!$category) {
            throw $this->createNotFoundException('Sección no encontrada');
        }


        $usedMachineries = $entityManager->getRepository(UsedMachinery::class)->findBy(['category' => $category]);

        return $this->render('used_machinery/view.html.twig', [
            'usedMachineries' => $usedMachineries,
            'section' => $section,
        ]);
    }

    #[Route('/used-machinery/detail/{id}', name: 'used_machinery_detail')]
    public function detail(int $id, EntityManagerInterface $entityManager): Response
    {
        $usedMachinery = $entityManager->getRepository(UsedMachinery::class)->find($id);

        if (!$usedMachinery) {
            throw $this->createNotFoundException('Maquinaria no encontrada');
        }

        return $this->render('used_machinery/detail.html.twig', [
            'usedMachinery' => $usedMachinery,
        ]);
    }
}