<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductMovement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use DateTime;
use DateTimeZone;

class ProductMovementService
{
    private $entityManager;
    private $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function recordMovement(
        Product $product,
        string $movementType,
        int $quantityChange,
        int $previousStock,
        int $newStock,
        ?string $description = null,
        ?string $performedBy = null
    ): ProductMovement {
        $argentinaTime = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
    
        $movement = new ProductMovement();
        $movement->setProduct($product);
        $movement->setMovementType($movementType);
        $movement->setQuantity($quantityChange);
        $movement->setPreviousStock($previousStock);
        $movement->setNewStock($newStock);
        $movement->setPerformedBy($performedBy ?? 'NicoO1997');
        $movement->setDescription($description);
        $movement->setCreatedAt($argentinaTime);
    
        $this->entityManager->persist($movement);
        $this->entityManager->flush();
        
        return $movement;
    }

    /**
     * Registra la entrega física de una reserva (sin afectar al stock)
     * 
     * @param Product $product El producto entregado
     * @param int $quantity La cantidad entregada
     * @param string|null $description Descripción opcional del movimiento
     * @return void
     */
    public function recordPhysicalDelivery(
        Product $product,
        int $quantity,
        ?string $description = null
    ): void {
        $currentStock = $product->getQuantity();
        
        $this->recordMovement(
            $product,
            'delivery',
            $quantity, // Guarda la cantidad entregada aquí (sin signo negativo)
            $currentStock,
            $currentStock,
            $description ?? sprintf('Entrega física de %d unidades de producto reservado', $quantity)
        );
    }

    public function recordEntry(Product $product, int $quantity, ?string $description = null): void
    {
        $previousStock = $product->getQuantity();
        $newStock = $previousStock + $quantity;

        $this->recordMovement(
            $product,
            ProductMovement::TYPE_ENTRY,
            $quantity,
            $previousStock,
            $newStock,
            $description ?? sprintf('Entrada de stock: +%d unidades', $quantity)
        );
    }

    public function recordSale(Product $product, int $quantity, ?string $description = null): void
    {
        $previousStock = $product->getQuantity();
        $newStock = $previousStock - $quantity;

        $this->recordMovement(
            $product,
            ProductMovement::TYPE_SALE,
            -$quantity,
            $previousStock,
            $newStock,
            $description ?? sprintf('Venta de stock: -%d unidades', $quantity)
        );
    }

    public function recordDeletion(Product $product, ?string $description = null): void
    {
        $previousStock = $product->getQuantity();
        $newStock = 0;

        $this->recordMovement(
            $product,
            ProductMovement::TYPE_DELETION,
            -$previousStock,
            $previousStock,
            $newStock,
            $description ?? 'Eliminación del producto'
        );
    }

    public function recordReservedSale(
        Product $product,
        int $quantity,
        ?string $description = null,
        ?string $orderId = null
    ): void {
        $previousStock = $product->getQuantity();
        $newStock = $previousStock - $quantity;
    
        // Primero actualizar la cantidad del producto ANTES de registrar el movimiento
        $product->setQuantity($newStock);
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        
        // Ahora registrar el movimiento después de haber actualizado el producto
        $this->recordMovement(
            $product,
            ProductMovement::TYPE_RESERVED_SALE,
            -$quantity,
            $previousStock,  // El stock original antes de la actualización
            $newStock,       // El nuevo stock después de la actualización
            $description ?? sprintf('Reserva de %d unidades', $quantity),
            $orderId ?? 'NicoO1997'
        );
    }

    public function recordPermanentDeletion(Product $product, ?string $description = null): void
    {
        $previousStock = $product->getQuantity();
        
        $this->recordMovement(
            $product,
            ProductMovement::TYPE_PERMANENT_DELETE,
            -$previousStock,
            $previousStock,
            0,
            $description ?? sprintf(
                'Eliminación permanente del producto %s realizada por %s',
                $product->getName(),
                'SantiAragon'
            ),
            'SantiAragon'
        );
    }

    public function recordAdjustment(Product $product, int $newQuantity, ?string $description = null): void
    {
        $previousStock = $product->getQuantity();
        $quantityChange = $newQuantity - $previousStock;

        $this->recordMovement(
            $product,
            ProductMovement::TYPE_ADJUSTMENT,
            $quantityChange,
            $previousStock,
            $newQuantity,
            $description ?? sprintf('Ajuste de stock de %d a %d unidades', $previousStock, $newQuantity)
        );
    }

    public function recordEdit(Product $product, int $newQuantity, ?string $description = null): void
    {
        $originalStock = $product->getQuantity();
        $difference = $newQuantity - $originalStock;

        $this->recordMovement(
            $product,
            ProductMovement::TYPE_EDIT,
            $difference,
            $originalStock,
            $newQuantity,
            $description ?? sprintf(
                'Modificación de stock: %s%d unidades. Stock anterior: %d, Stock nuevo: %d',
                $difference > 0 ? '+' : '',
                $difference,
                $originalStock,
                $newQuantity
            )
        );
    }

    /**
     * Obtiene las estadísticas de ventas por mes
     * 
     * @param int $year El año a consultar
     * @param int $month El mes a consultar (1-12)
     * @return array Los productos más vendidos y sus cantidades
     */
    public function getTopSellingProductsByMonth(int $year, int $month): array
    {
        // Validar mes
        $month = max(1, min(12, $month));
        
        // Construir fechas para el período
        $startDate = new \DateTime("$year-$month-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month')->setTime(23, 59, 59);
        
        // Crear consulta
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p.id, p.name as product_name, SUM(m.quantity) AS total_quantity')
           ->from(ProductMovement::class, 'm')
           ->join('m.product', 'p')
           ->where('m.movementType = :saleType')
           ->andWhere('m.createdAt BETWEEN :start AND :end')
           ->setParameter('saleType', ProductMovement::TYPE_SALE)
           ->setParameter('start', $startDate)
           ->setParameter('end', $endDate)
           ->groupBy('p.id, p.name')
           ->orderBy('total_quantity', 'DESC');
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Calcula el monto total vendido en un mes
     * 
     * @param int $year El año a consultar
     * @param int $month El mes a consultar (1-12)
     * @return float El monto total vendido
     */
    public function getTotalAmountSoldByMonth(int $year, int $month): float
    {
        // Validar mes
        $month = max(1, min(12, $month));
        
        // Construir fechas para el período
        $startDate = new \DateTime("$year-$month-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month')->setTime(23, 59, 59);
        
        // Crear consulta
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COALESCE(SUM(ABS(m.quantity) * p.price), 0) as total_amount')
           ->from(ProductMovement::class, 'm')
           ->join('m.product', 'p')
           ->where('m.movementType = :saleType')
           ->andWhere('m.createdAt BETWEEN :start AND :end')
           ->setParameter('saleType', ProductMovement::TYPE_SALE)
           ->setParameter('start', $startDate)
           ->setParameter('end', $endDate);
        
        $result = $qb->getQuery()->getSingleScalarResult();
        return is_numeric($result) ? (float) $result : 0.0;
    }
    
    /**
     * Obtiene estadísticas completas de ventas para un mes específico
     * 
     * @param int $year El año a consultar
     * @param int $month El mes a consultar (1-12)
     * @return array Estadísticas completas del mes
     */
    public function getMonthlyStatistics(int $year, int $month): array
    {
        $topSellingProducts = $this->getTopSellingProductsByMonth($year, $month);
        $totalAmount = $this->getTotalAmountSoldByMonth($year, $month);
        
        // Calculamos fechas para incluir en la respuesta
        $startDate = new \DateTime("$year-$month-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');
        
        return [
            'period' => [
                'year' => $year,
                'month' => $month,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'top_selling_products' => $topSellingProducts,
            'total_amount' => $totalAmount,
            'total_products_sold' => count($topSellingProducts),
            'generated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
    }
    
    /**
     * Compara ventas entre dos meses diferentes
     * 
     * @param int $year1 Primer año a comparar
     * @param int $month1 Primer mes a comparar (1-12)
     * @param int $year2 Segundo año a comparar
     * @param int $month2 Segundo mes a comparar (1-12)
     * @return array Comparación entre los dos meses
     */
    public function compareMonthlySales(int $year1, int $month1, int $year2, int $month2): array
    {
        $stats1 = $this->getMonthlyStatistics($year1, $month1);
        $stats2 = $this->getMonthlyStatistics($year2, $month2);
        
        // Calculamos diferencias
        $amountDifference = $stats2['total_amount'] - $stats1['total_amount'];
        $percentChange = $stats1['total_amount'] > 0 
            ? ($amountDifference / $stats1['total_amount']) * 100 
            : 0;
        
        return [
            'first_month' => $stats1,
            'second_month' => $stats2,
            'comparison' => [
                'amount_difference' => $amountDifference,
                'percent_change' => round($percentChange, 2),
                'increased' => $amountDifference > 0,
            ]
        ];
    }
    
    /**
     * Obtiene las ventas anuales agrupadas por mes
     * 
     * @param int $year El año a consultar
     * @return array Ventas mensuales para el año
     */
    public function getYearlySalesByMonth(int $year): array
    {
        $result = [];
        
        // Obtenemos estadísticas para cada mes del año
        for ($month = 1; $month <= 12; $month++) {
            $monthName = date('F', mktime(0, 0, 0, $month, 10));
            $result[$monthName] = $this->getTotalAmountSoldByMonth($year, $month);
        }
        
        return $result;
    }

    /**
     * Registra una reserva de producto (reduce el stock pero con etiqueta específica)
     * 
     * @param Product $product El producto a reservar
     * @param int $quantity La cantidad a reservar
     * @param string|null $description Descripción opcional de la reserva
     * @param string|null $orderId Identificador opcional del usuario o la orden
     * @return void
     */
    public function recordReservation(
        Product $product,
        int $quantity,
        ?string $description = null,
        ?string $orderId = null
    ): void {
        $previousStock = $product->getQuantity();
        $newStock = $previousStock - $quantity;

        // Actualizar la cantidad del producto ANTES de registrar el movimiento
        $product->setQuantity($newStock);
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        
        // Registrar el movimiento con tipo específico para reservas
        $this->recordMovement(
            $product,
            ProductMovement::TYPE_RESERVATION, // Usar la constante específica para reservas
            -$quantity,
            $previousStock,
            $newStock,
            $description ?? 'Producto reservado', // Utilizar exactamente "Producto reservado"
            $orderId ?? 'NicoO1997'
        );
    }
}