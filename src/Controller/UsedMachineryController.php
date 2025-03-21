<?php

namespace App\Controller;

use App\Entity\UsedMachinery;
use App\Form\UsedMachineryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Category;

class UsedMachineryController extends AbstractController
{
    #[Route('/view-used-machinery', name: 'app_view_used_machinery')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $queryBuilder = $entityManager->getRepository(UsedMachinery::class)
            ->createQueryBuilder('m')
            ->leftJoin('m.category', 'c')
            ->addSelect('c')
            ->where('m.category IS NOT NULL')
            ->orderBy('m.id', 'DESC');
        
        $usedMachineries = $queryBuilder->getQuery()->getResult();

        return $this->render('used_machinery/view.html.twig', [
            'usedMachineries' => $usedMachineries,
            'isVendedor' => $this->isGranted('ROLE_VENDEDOR'),
        ]);
    }

    #[Route('/used-machinery/detail/{id}', name: 'used_machinery_detail', methods: ['GET'])]
    public function detail(int $id, EntityManagerInterface $entityManager): Response
    {
        $machinery = $entityManager->getRepository(UsedMachinery::class)->find($id);
        
        if (!$machinery) {
            $this->addFlash('error', 'Maquinaria no encontrada');
            return $this->redirectToRoute('app_view_used_machinery');
        }
        
        return $this->render('used_machinery/detail.html.twig', [
            'machinery' => $machinery,
            'isVendedor' => $this->isGranted('ROLE_VENDEDOR'),
            'isInvitado' => $this->isGranted('ROLE_INVITADO'),
        ]);
    }

    #[Route('/add-used-machinery', name: 'app_add_used_machinery_page', methods: ['GET', 'POST'])]
    public function showAddUsedMachineryForm(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_VENDEDOR', null, 'No tienes permiso para acceder a esta página');

        $machinery = new UsedMachinery();
        $form = $this->createForm(UsedMachineryType::class, $machinery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $imageFiles = $form->get('imageFilenames')->getData();
                $filenames = [];

                if ($imageFiles) {
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/imagenes';

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $count = 0;
                    foreach ($imageFiles as $imageFile) {
                        if ($count >= 5) {
                            break;
                        }

                        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = preg_replace('/[^A-Za-z0-9]/', '-', $originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                        try {
                            $imageFile->move($uploadDir, $newFilename);
                            $filenames[] = $newFilename;
                            $count++;
                        } catch (\Exception $e) {
                            $this->addFlash('error', 'Error al subir la imagen: ' . $e->getMessage());
                        }
                    }
                }

                if (empty($filenames)) {
                    $filenames = ['default.jpg'];
                }

                $machinery->setImageFilenames($filenames);

                if ($machinery->getIsNew()) {
                    $machinery->setHoursOfUse(null);
                    $machinery->setLastService(null);
                    $machinery->setManufacturingDate(null);
                }

                $entityManager->persist($machinery);
                $entityManager->flush();

                $this->addFlash('success', 'Maquinaria agregada con éxito.');
                return $this->redirectToRoute('app_view_used_machinery');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al guardar la maquinaria: ' . $e->getMessage());
            }
        } else if ($form->isSubmitted() && !$form->isValid()) {
            $errors = $form->getErrors(true, true);
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getOrigin()->getName() . ': ' . $error->getMessage());
            }
        }

        return $this->render('used_machinery/add.html.twig', [
            'form' => $form->createView(),
            'isVendedor' => $this->isGranted('ROLE_VENDEDOR'),
        ]);
    }
    
    #[Route('/edit-used-machinery/{id}', name: 'used_machinery_edit', methods: ['GET', 'POST'])]
    public function editUsedMachinery(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_VENDEDOR', null, 'No tienes permiso para acceder a esta página');
        
        $machinery = $entityManager->getRepository(UsedMachinery::class)->find($id);
        
        if (!$machinery) {
            $this->addFlash('error', 'Maquinaria no encontrada');
            return $this->redirectToRoute('app_view_used_machinery');
        }
        
        $currentImages = $machinery->getImageFilenames();
        
        $form = $this->createForm(UsedMachineryType::class, $machinery);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $imageFiles = $form->get('imageFilenames')->getData();
                
                if ($imageFiles) {
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/imagenes';
                    
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $count = 0;
                    $filenames = [];
                    
                    foreach ($imageFiles as $imageFile) {
                        if ($count >= 5) {
                            break;
                        }
                        
                        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = preg_replace('/[^A-Za-z0-9]/', '-', $originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                        
                        try {
                            $imageFile->move($uploadDir, $newFilename);
                            $filenames[] = $newFilename;
                            $count++;
                        } catch (\Exception $e) {
                            $this->addFlash('error', 'Error al subir la imagen: ' . $e->getMessage());
                        }
                    }
                    
                    if (!empty($filenames)) {
                        foreach ($currentImages as $oldImage) {
                            if ($oldImage !== 'default.jpg') {
                                $oldImagePath = $uploadDir . '/' . $oldImage;
                                if (file_exists($oldImagePath)) {
                                    unlink($oldImagePath);
                                }
                            }
                        }
                        $machinery->setImageFilenames($filenames);
                    }
                } 
                
                if ($machinery->getIsNew()) {
                    $machinery->setHoursOfUse(null);
                    $machinery->setLastService(null);
                    $machinery->setManufacturingDate(null);
                }
                
                $entityManager->flush();
                
                $this->addFlash('success', 'Maquinaria actualizada con éxito');
                return $this->redirectToRoute('app_view_used_machinery');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al actualizar la maquinaria: ' . $e->getMessage());
            }
        } else if ($form->isSubmitted()) {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }
        
        return $this->render('used_machinery/edit.html.twig', [
            'form' => $form->createView(),
            'machinery' => $machinery,
            'isVendedor' => $this->isGranted('ROLE_VENDEDOR'),
        ]);
    }
    
    #[Route('/delete-machinery/{id}', name: 'used_machinery_delete', methods: ['GET', 'POST'])]
    public function deleteMachinery(int $id, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_VENDEDOR', null, 'No tienes permiso para realizar esta acción');
        
        $machinery = $entityManager->getRepository(UsedMachinery::class)->find($id);
        
        if (!$machinery) {
            $this->addFlash('error', 'Maquinaria no encontrada');
            return $this->redirectToRoute('app_view_used_machinery');
        }
        
        try {
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/imagenes';
            $images = $machinery->getImageFilenames();
            
            foreach ($images as $image) {
                if ($image !== 'default.jpg') {
                    $imagePath = $uploadDir . '/' . $image;
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
            }
            
            $entityManager->remove($machinery);
            $entityManager->flush();
            
            $this->addFlash('success', 'Maquinaria eliminada correctamente');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al eliminar la maquinaria: ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('app_view_used_machinery');
    }

    #[Route('/usedMachinery/category/{category}', name: 'used_machinery_category')]
    public function category(string $category, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $usedMachineries = $entityManager->getRepository(UsedMachinery::class)->findBy(['category' => $category]);
    
        return $this->render('used_machinery/view.html.twig', [
            'usedMachineries' => $usedMachineries,
            'isVendedor' => $this->isGranted('ROLE_VENDEDOR'),
            'isInvitado' => $this->isGranted('ROLE_INVITADO'),
        ]);
    }

    #[Route('/usedMachinery/section/{section}', name: 'used_machinery_section')]
    public function section(string $section, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $category = $entityManager->getRepository(Category::class)
            ->findOneBy(['name' => $section]);
    
        if (!$category) {
            throw $this->createNotFoundException('Sección no encontrada');
        }
    
        $queryBuilder = $entityManager->getRepository(UsedMachinery::class)
            ->createQueryBuilder('m')
            ->leftJoin('m.category', 'c')
            ->addSelect('c')
            ->where('m.category = :category')
            ->setParameter('category', $category)
            ->orderBy('m.id', 'DESC');
    
        $usedMachineries = $queryBuilder->getQuery()->getResult();

        if (!$this->isGranted('ROLE_VENDEDOR') && !$this->isGranted('ROLE_INVITADO')) {
            $usedMachineries = array_filter($usedMachineries, function($machinery) {
                return $machinery->getPrice() !== null;
            });
        }
    
        return $this->render('used_machinery/view.html.twig', [
            'usedMachineries' => $usedMachineries,
            'section' => $section,
            'isVendedor' => $this->isGranted('ROLE_VENDEDOR'),
            'isInvitado' => $this->isGranted('ROLE_INVITADO'),
        ]);
    }
}