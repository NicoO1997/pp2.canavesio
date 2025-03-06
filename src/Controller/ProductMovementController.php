<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductMovement;
use App\Repository\ProductMovementRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\MovementFilterType;
use DateTime;
use DateTimeZone;

class ProductMovementController extends AbstractController
{
    #[Route('/historial-movimientos', name: 'product_movements')]
    public function index(
        Request $request, 
        ProductMovementRepository $movementRepository,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(MovementFilterType::class);
        $form->handleRequest($request);

        $queryBuilder = $movementRepository->createQueryBuilder('m')
            ->leftJoin('m.product', 'p')
            ->addSelect('p')
            ->orderBy('m.createdAt', 'DESC');

        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            
            if ($filters['product']) {
                $queryBuilder->andWhere('m.product = :product')
                    ->setParameter('product', $filters['product']);
            }

            if ($filters['type']) {
                $queryBuilder->andWhere('m.movementType = :type')
                    ->setParameter('type', $filters['type']);
            }

            if ($filters['dateFrom']) {
                $dateFrom = $filters['dateFrom'];
                $dateFrom->setTime(0, 0, 0);
                $queryBuilder->andWhere('m.createdAt >= :dateFrom')
                    ->setParameter('dateFrom', $dateFrom);
            }

            if ($filters['dateTo']) {
                $dateTo = $filters['dateTo'];
                $dateTo->setTime(23, 59, 59);
                $queryBuilder->andWhere('m.createdAt <= :dateTo')
                    ->setParameter('dateTo', $dateTo);
            }
        }

        $movements = $queryBuilder->getQuery()->getResult();
        $products = $productRepository->findAll();

        return $this->render('product/movements.html.twig', [
            'movements' => $movements,
            'products' => $products,
            'form' => $form->createView(),
            'currentDateTime' => new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires')),
            'currentUser' => 'NicoO1997'
        ]);
    }
}