<?php

namespace App\Controller;

use App\Entity\UsedMachinery;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class AdminUsedMachineryController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/admin/used-machinery', name: 'admin_view_used_machinery')]
    public function viewUsedMachinery(EntityManagerInterface $entityManager): Response
    {
        $usedMachineries = $entityManager->getRepository(UsedMachinery::class)->findAll();
        $this->logger->info('Retrieved ' . count($usedMachineries) . ' machineries');

        return $this->render('used_machinery/AdminView.html.twig', [
            'usedMachineries' => $usedMachineries,
        ]);
    }

    #[Route('/admin/used-machinery/update/{id}', name: 'admin_update_used_machinery', methods: ['POST'])]
    public function updateUsedMachinery($id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->logger->info('Updating machinery with ID: ' . $id);
        
        $this->logger->info('Received data: ' . json_encode($request->request->all()));

        $machinery = $entityManager->getRepository(UsedMachinery::class)->find($id);
    
        if (!$machinery) {
            $this->logger->error('No machinery found for id ' . $id);
            throw $this->createNotFoundException('No machinery found for id ' . $id);
        }

        // Actualizar los datos del formulario
        $machinery->setMachineryName($request->request->get('machineryName'));
        $machinery->setBrand($request->request->get('brand'));
        $machinery->setYearsOld($request->request->get('yearsOld'));
        $machinery->setMonths($request->request->get('months'));
        $machinery->setHoursOfUse($request->request->get('hoursOfUse'));
        $machinery->setLastService(new \DateTime($request->request->get('lastService')));
        $machinery->setPrice($request->request->get('price'));
    
        // Manejo de la imagen subida (solo si se ha subido una nueva imagen)
        $image = $request->files->get('image');
        if ($image) {
            $this->logger->info('New image uploaded for machinery ' . $id);
            // Generar un nuevo nombre de archivo único y mover la imagen
            $newFilename = uniqid() . '.' . $image->guessExtension();
            $image->move($this->getParameter('images_directory'), $newFilename);
    
            // Borrar la imagen anterior si existe
            if ($machinery->getImageFilename()) {
                $oldImage = $this->getParameter('images_directory') . '/' . $machinery->getImageFilename();
                if (file_exists($oldImage)) {
                    unlink($oldImage);  // Eliminar la imagen anterior
                    $this->logger->info('Old image deleted for machinery ' . $id);
                }
            }
    
            $machinery->setImageFilename($newFilename); // Guardar el nuevo nombre de imagen
        }
    
        // Guardar los cambios
        $entityManager->flush();
        $this->logger->info('Database updated successfully for machinery ID: ' . $id);
    
        // Redirigir nuevamente a la página de visualización
        return $this->redirectToRoute('admin_view_used_machinery');
    }    

    #[Route('/used-machinery/delete/{id}', name: 'admin_delete_used_machinery')]
    public function deleteUsedMachinery(EntityManagerInterface $entityManager, UsedMachinery $usedMachinery): Response
    {
        $this->logger->info('Deleting machinery with ID: ' . $usedMachinery->getId());
        $entityManager->remove($usedMachinery);
        $entityManager->flush();

        $this->logger->info('Machinery deleted successfully');
        $this->addFlash('success', 'Maquinaria eliminada con éxito.');
        return $this->redirectToRoute('admin_view_used_machinery');
    }
}