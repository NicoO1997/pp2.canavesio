<?php

namespace App\Controller;

use App\Entity\ProductMovement;
use App\Form\MovementFilterType;
use App\Form\MonthYearSelectorType;
use App\Repository\ProductMovementRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;
use DateTimeZone;

class ProductMovementController extends AbstractController
{
    #[Route('/estadisticas-ventas', name: 'sales_statistics')]
    public function salesStatistics(Request $request, EntityManagerInterface $em): Response
    {
        $month = (int)date('m');
        $year = (int)date('Y');
        $filterByYear = false;
        $filterByDateRange = false;
        $startDate = null;
        $endDate = null;
        
        $form = $this->createForm(MonthYearSelectorType::class, [
            'month' => $month,
            'year' => $year,
            'date_range' => 'month_year',
            'start_date' => new \DateTime('first day of this month'),
            'end_date' => new \DateTime('last day of this month'),
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            // Verificar qué tipo de filtro se está utilizando
            if ($data['date_range'] === 'date_range') {
                $filterByDateRange = true;
                $startDate = $data['start_date'];
                $endDate = $data['end_date'];
                
                // Asegurar que end_date incluya todo el día
                if ($endDate) {
                    $endDate->setTime(23, 59, 59);
                }
            } else {
                // Si no es filtro por rango de fechas, usamos el filtro por mes/año
                $year = (int)$data['year'];
                
                // Verificar qué botón fue presionado usando el array de datos de la solicitud
                if ($request->request->has('month_year_selector')) {
                    $formData = $request->request->all('month_year_selector');
                    // Si el botón filter_year fue presionado, su valor aparecerá en el array de datos
                    $filterByYear = isset($formData['filter_year']);
                }
                
                if (!$filterByYear) {
                    // Solo si no estamos filtrando por año, usamos el mes seleccionado
                    $month = (int)$data['month'];
                }
            }
        } else {
            // Manejo de parámetros GET para poder compartir URLs
            $requestedMonth = $request->query->get('month');
            $requestedYear = $request->query->get('year');
            $requestedFilterByYear = $request->query->get('filterByYear');
            $requestedStartDate = $request->query->get('start_date');
            $requestedEndDate = $request->query->get('end_date');
            
            if ($requestedStartDate && $requestedEndDate) {
                $filterByDateRange = true;
                $startDate = new \DateTime($requestedStartDate);
                $endDate = new \DateTime($requestedEndDate);
                $endDate->setTime(23, 59, 59);
            } else {
                if ($requestedMonth) {
                    $month = (int)$requestedMonth;
                }
                
                if ($requestedYear) {
                    $year = (int)$requestedYear;
                }
                
                if ($requestedFilterByYear === '1') {
                    $filterByYear = true;
                }
            }
        }
        
        $conn = $em->getConnection();
        
        // Configurar las fechas para la consulta
        if ($filterByDateRange && $startDate && $endDate) {
            $startDateString = $startDate->format('Y-m-d 00:00:00');
            $endDateString = $endDate->format('Y-m-d 23:59:59');
        } else if ($filterByYear) {
            $startDateString = sprintf('%d-01-01 00:00:00', $year);
            $endDateString = sprintf('%d-12-31 23:59:59', $year);
            
            // Cuando filtramos por año, ponemos mes a 0 para la plantilla
            $month = 0;
        } else {
            $month = max(1, min(12, $month)); // Asegurar que el mes esté entre 1 y 12
            $daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
            $startDateString = sprintf('%d-%02d-01 00:00:00', $year, $month);
            $endDateString = sprintf('%d-%02d-%d 23:59:59', $year, $month, $daysInMonth);
        }
        
        // Consulta para obtener estadísticas de ventas
        $sqlStats = "
            SELECT p.id, p.name as product_name, ABS(SUM(m.quantity)) AS total_quantity, 
                ABS(SUM(m.quantity)) * p.price AS total_amount_per_product
            FROM product_movement m
            JOIN product p ON m.product_id = p.id
            WHERE m.movement_type = 'sale'
            AND m.created_at BETWEEN ? AND ?
            GROUP BY p.id, p.name, p.price
            ORDER BY total_quantity DESC
        ";
        
        $stmt = $conn->executeQuery($sqlStats, [
            $startDateString,
            $endDateString
        ]);
        
        $statistics = $stmt->fetchAllAssociative();
        
        // Consulta para obtener el monto total
        $sqlTotal = "
            SELECT COALESCE(SUM(ABS(m.quantity) * p.price), 0) as total_amount
            FROM product_movement m
            JOIN product p ON m.product_id = p.id
            WHERE m.movement_type = 'sale'
            AND m.created_at BETWEEN ? AND ?
        ";
        
        $totalAmount = $conn->executeQuery($sqlTotal, [
            $startDateString,
            $endDateString
        ])->fetchOne();
        
        return $this->render('product/sales_statistics.html.twig', [
            'month' => $month,
            'year' => $year,
            'startDate' => $startDateString,
            'endDate' => $endDateString,
            'statistics' => $statistics,
            'totalAmount' => $totalAmount ?: 0,
            'form' => $form->createView(),
            'currentDateTime' => new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires')),
            'currentUser' => $this->getUser() ? $this->getUser()->getUserIdentifier() : 'Guest',
            'filterByYear' => $filterByYear,
            'filterByDateRange' => $filterByDateRange,
            'startDateObj' => $startDate,
            'endDateObj' => $endDate
        ]);
    }

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
        
        foreach ($movements as $movement) {
            if ($movement->getNewStock() === $movement->getPreviousStock() && 
                $movement->getMovementType() !== ProductMovement::TYPE_RESERVED_SALE) {
                $previousStock = $movement->getPreviousStock();
                $quantity = $movement->getQuantity();
                
                // Actualizar el newStock según el tipo de movimiento
                switch ($movement->getMovementType()) {
                    case ProductMovement::TYPE_ENTRY:
                        $movement->setNewStock($previousStock + abs($quantity));
                        break;
                    case ProductMovement::TYPE_SALE:
                    case ProductMovement::TYPE_DELETION:
                    case ProductMovement::TYPE_PERMANENT_DELETE:
                        $movement->setNewStock($previousStock - abs($quantity));
                        break;
                    case ProductMovement::TYPE_EDIT:
                    case ProductMovement::TYPE_ADJUSTMENT:
                        $movement->setNewStock($previousStock + $quantity);
                        break;
                }
                
                // Persistir los cambios a la base de datos
                $entityManager->persist($movement);
            }
        }
        
        // Aplicar los cambios a la base de datos
        $entityManager->flush();

        $currentUser = $this->getUser();
        $currentUsername = $currentUser ? $currentUser->getUserIdentifier() : 'Guest';

        return $this->render('product/movements.html.twig', [
            'movements' => $movements,
            'form' => $form->createView(),
            'currentDateTime' => new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires')),
            'currentUser' => $currentUsername
        ]);
    }
}