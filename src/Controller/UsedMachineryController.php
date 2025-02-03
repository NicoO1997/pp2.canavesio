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
        // Obtenemos todas las maquinarias usadas desde la base de datos
        $usedMachineries = $entityManager->getRepository(UsedMachinery::class)->findAll();

        // Renderizamos la vista pasando los datos de las maquinarias
        return $this->render('used_machinery/view.html.twig', [
            'usedMachineries' => $usedMachineries,
        ]);
    }

    #[Route('/add-used-machinery', name: 'app_add_used_machinery_page')]
    public function showAddUsedMachineryForm(Request $request, EntityManagerInterface $entityManager): Response
    {
        $usedMachinery = new UsedMachinery();
        $form = $this->createForm(UsedMachineryType::class, $usedMachinery);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Manejar la subida de la imagen
            $imageFile = $form->get('imageFilename')->getData();
            if ($imageFile) {
                $extension = $imageFile->guessExtension();
                $newFilename = uniqid() . '.' . $extension;

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $usedMachinery->setImageFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error al subir la imagen.');
                    return $this->redirectToRoute('app_add_used_machinery_page');
                }
            }

            $entityManager->persist($usedMachinery);
            $entityManager->flush();

            $this->addFlash('success', 'Maquinaria usada añadida con éxito.');
            return $this->redirectToRoute('app_view_used_machinery');
        }

        return $this->render('used_machinery/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/add-used-machinery/submit', name: 'app_add_used_machinery', methods: ['GET', 'POST'])]
    public function addUsedMachinery(Request $request, EntityManagerInterface $entityManager): Response
    {
        $machineryName = $request->request->get('machinery_name');
        $brand = $request->request->get('brand');
        $yearsOld = $request->request->get('years_old');
        $months = $request->request->get('months');
        $hoursOfUse = $request->request->get('hours_of_use');
        $lastService = $request->request->get('last_service');
        $price = $request->request->get('price');

        // Validaciones para campos obligatorios
        if (empty($machineryName) || empty($brand) || empty($yearsOld) || empty($months) || empty($hoursOfUse) || empty($lastService)) {
            $this->addFlash('error', 'Error: Todos los campos obligatorios deben estar completos.');
            return $this->redirectToRoute('app_add_used_machinery_page');
        }

        // Validar el precio
        $price = $price !== null ? (float)$price : null;
        if ($price !== null && $price < 0) {
            $this->addFlash('error', 'Error: El precio no puede ser menor de cero.');
            return $this->redirectToRoute('app_add_used_machinery_page');
        }

        // Validar y manejar la imagen
        $imageFile = $request->files->get('imageFilename')->getData();
        if (!$imageFile instanceof UploadedFile || !$imageFile->isValid()) {
            $this->addFlash('error', 'Error: Se requiere una imagen válida para la maquinaria.');
            return $this->redirectToRoute('app_add_used_machinery_page');
        }

        // Verificar extensión de archivo
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $extension = $imageFile->guessExtension();
        if (!in_array($extension, $allowedExtensions)) {
            $this->addFlash('error', 'Error: La extensión de la imagen no es válida. Permitir solo jpg, jpeg, png, gif.');
            return $this->redirectToRoute('app_add_used_machinery_page');
        }

        $newFilename = uniqid() . '.' . $extension;

        try {
            $imageFile->move(
                $this->getParameter('images_directory'),
                $newFilename
            );
        } catch (FileException $e) {
            $this->addFlash('error', 'Error al subir la imagen.');
            return $this->redirectToRoute('app_add_used_machinery_page');
        }

        // Crear una nueva instancia de la entidad UsedMachinery
        $usedMachinery = new UsedMachinery();
        $usedMachinery->setMachineryName($machineryName);
        $usedMachinery->setBrand($brand);
        $usedMachinery->setYearsOld((int)$yearsOld);
        $usedMachinery->setMonths((int)$months);  // Asignar los meses
        $usedMachinery->setHoursOfUse((int)$hoursOfUse);
        $usedMachinery->setLastService(new \DateTime($lastService));
        $usedMachinery->setPrice($price);
        $usedMachinery->setImageFilename($newFilename);

        // Persistir la maquinaria en la base de datos
        $entityManager->persist($usedMachinery);
        $entityManager->flush();

        // Mensaje de éxito y redirección
        $this->addFlash('success', "Maquinaria usada añadida con éxito.");

        return $this->redirectToRoute('app_add_used_machinery_page');
    }

    #[Route('/usedMachinery/category/{category}', name: 'used_machinery_category')]
    public function category(
        string $category,
        EntityManagerInterface $entityManager,
        UserFavoriteProductRepository $userFavoriteProductRepository
    ): Response {
        $user = $this->getUser();
        $usedMachineries = $entityManager->getRepository(UsedMachinery::class)->findBy(['category' => $category]);

        // Obtener IDs de productos favoritos del usuario actual
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
        // Mapear nombres amigables de secciones a las categorías en la base de datos
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


        // Obtener productos relacionados con la categoría
        $usedMachineries = $entityManager->getRepository(UsedMachinery::class)->findBy(['category' => $category]);

        return $this->render('used_machinery/section.html.twig', [
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
