<?php
// src/Controller/PurchaseController.php

namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use App\Entity\Product;
use App\Entity\Cart;
use App\Entity\ProductMovement;
use App\Service\ProductMovementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use DateTimeZone;
use DateTime;

class PurchaseController extends AbstractController
{
    private $security;
    private $productMovementService;
    public function __construct(
        Security $security,
        ProductMovementService $productMovementService
    ) {
        $this->security = $security;
        $this->productMovementService = $productMovementService;
    }

    #[Route('/purchase/simulate/{productId}', name: 'purchase_simulate')]
    public function simulatePayment(
        int $productId,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $product = $entityManager->getRepository(Product::class)->find($productId);

        if (!$product) {
            throw $this->createNotFoundException('Producto no encontrado');
        }

        // Verificar si el usuario está autenticado
        if (!$this->security->isGranted('ROLE_USUARIO')) {
            $this->addFlash('error', 'Debes iniciar sesión para realizar una compra');
            return $this->redirectToRoute('app_login');
        }

        $quantity = $request->query->get('quantity', 1);

        return $this->render('purchase/payment.html.twig', [
            'product' => $product,
            'quantity' => $quantity,
            'total' => $product->getPrice() * $quantity
        ]);
    }

    // En PurchaseController.php, modificar el método simulateCartPayment()

    #[Route('/purchase/simulate-cart', name: 'purchase_simulate_cart')]
    public function simulateCartPayment(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Debes iniciar sesión para realizar una compra');
        }

        // Obtener el carrito actual del usuario
        $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (!$cart || $cart->getCartProductOrders()->isEmpty()) {
            throw $this->createNotFoundException('El carrito está vacío');
        }

        try {
            // Calcular el total primero
            $total = 0;
            $purchaseItems = []; // Array para almacenar los items de la compra
            $cartItems = []; // Array para los detalles de la compra

            foreach ($cart->getCartProductOrders() as $cartProductOrder) {
                $product = $cartProductOrder->getProduct();
                $quantity = $cartProductOrder->getQuantity();

                // Verificar que la cantidad sea válida
                if (!$quantity || $quantity <= 0) {
                    throw new \InvalidArgumentException('La cantidad debe ser mayor a 0 para ' . $product->getName());
                }

                // Solo verificar stock para productos que no son de reserva
                if (!$cartProductOrder->isFromReservation()) {
                    if ($product->getQuantity() < $quantity) {
                        $this->addFlash('error', 'No hay suficiente stock para ' . $product->getName());
                        return $this->redirectToRoute('cart_view');
                    }
                }

                // Actualizar el total
                $total += $product->getPrice() * $quantity;

                // Preparar los items para el detalle de la compra
                if ($quantity > 0) {
                    $cartItems[] = [
                        'product_id' => $product->getId(),
                        'product_name' => $product->getName(),
                        'quantity' => $quantity,
                        'price' => $product->getPrice(),
                        'is_from_reservation' => $cartProductOrder->isFromReservation()
                    ];
                }
            }

            // Crear y configurar la compra
            $purchase = new Purchase();
            $purchase->setUser($user);
            $purchase->setTotalPrice($total); // Establecer el total antes de persistir
            $purchase->setStatus('completed');
            $purchase->setTransactionId(uniqid('TRX-'));
            $purchase->setPurchaseDate(new \DateTime('now'));

            // Persistir la compra primero
            $entityManager->persist($purchase);
            $entityManager->flush(); // Flush para obtener el ID de la compra

            // Crear y persistir los items de la compra
            foreach ($cart->getCartProductOrders() as $cartProductOrder) {
                $product = $cartProductOrder->getProduct();
                $quantity = $cartProductOrder->getQuantity();

                // Actualizar stock para productos que no son de reserva
                if (!$cartProductOrder->isFromReservation()) {
                    $previousStock = $product->getQuantity();
                    $newQuantity = $previousStock - $quantity;
                    $product->setQuantity($newQuantity);
                    
                    // Registrar el movimiento
                    $this->productMovementService->recordMovement(
                        $product,
                        ProductMovement::TYPE_SALE,
                        $quantity,
                        $previousStock,
                        $newQuantity,
                        sprintf('Venta realizada desde carrito. Transacción: %s', $purchase->getTransactionId())
                    );
                    
                    $entityManager->persist($product);
                } else {
                    // Para productos de reserva, registrar un movimiento especial
                    $this->productMovementService->recordMovement(
                        $product,
                        ProductMovement::TYPE_RESERVED_SALE,
                        $quantity,
                        $product->getQuantity(),
                        $product->getQuantity(), // No cambia el stock pero registra la venta
                        sprintf('Venta de producto reservado. Transacción: %s', $purchase->getTransactionId())
                    );
                }

                // Crear PurchaseItem
                $purchaseItem = new PurchaseItem();
                $purchaseItem->setProduct($product);
                $purchaseItem->setQuantity($quantity);
                $purchaseItem->setPrice($product->getPrice());
                $purchaseItem->setPurchase($purchase);
                $entityManager->persist($purchaseItem);
            }

            // Configurar detalles de pago
            $dateArgentina = new \DateTime('now', new \DateTimeZone('America/Argentina/Buenos_Aires'));
            $purchase->setPaymentDetails([
                'payment_method' => 'simulated_payment',
                'payment_date' => $dateArgentina->format('Y-m-d H:i:s'),
                'cart_items' => $cartItems,
                'created_by' => 'SantiAragon'
            ]);

            // Limpiar el carrito
            foreach ($cart->getCartProductOrders() as $order) {
                $entityManager->remove($order);
            }

            // Flush final para guardar todos los cambios
            $entityManager->flush();

            return $this->redirectToRoute('purchase_success', [
                'transactionId' => $purchase->getTransactionId()
            ]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Ocurrió un error al procesar la compra: ' . $e->getMessage());
            return $this->redirectToRoute('cart_view');
        }
    }

    #[Route('/purchase/cart/payment', name: 'purchase_cart_payment')]
    public function cartPayment(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Debes iniciar sesión para realizar una compra');
        }

        $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);
        if (!$cart || $cart->getCartProductOrders()->isEmpty()) {
            throw $this->createNotFoundException('El carrito está vacío');
        }

        // Calcular el total
        $total = 0;
        foreach ($cart->getCartProductOrders() as $order) {
            $total += $order->getProduct()->getPrice() * $order->getQuantity();
        }

        return $this->render('purchase/payment.html.twig', [
            'cart' => $cart,
            'total' => $total
        ]);
    }


    #[Route('/purchase/process', name: 'purchase_process', methods: ['POST'])]
    public function processPurchase(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->security->isGranted('ROLE_USUARIO')) {
            throw $this->createAccessDeniedException('Debes iniciar sesión para realizar una compra');
        }

        $data = $request->request->all();
        $isCartPurchase = $request->request->has('is_cart_purchase');

        // Validar datos de la tarjeta
        $errors = $this->validateCardData($data);
        if (!empty($errors)) {
            return $this->json([
                'success' => false,
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            if ($isCartPurchase) {
                // Procesar compra del carrito
                $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $this->getUser()]);
                if (!$cart || $cart->getCartProductOrders()->isEmpty()) {
                    throw new \Exception('El carrito está vacío');
                }

                // Calcular el total primero
                $total = 0;
                foreach ($cart->getCartProductOrders() as $cartProductOrder) {
                    $total += $cartProductOrder->getProduct()->getPrice() * $cartProductOrder->getQuantity();
                }

                // Crear y configurar la compra
                $purchase = new Purchase();
                $purchase->setUser($this->getUser());
                $purchase->setStatus('completed');
                $purchase->setTransactionId(uniqid('TRX-'));
                $purchase->setTotalPrice($total); // Establecer el total antes de persistir
                $purchase->setPurchaseDate(new DateTime('now'));
                
                // Persistir la compra
                $entityManager->persist($purchase);
                $entityManager->flush(); // Flush para obtener el ID de la compra

                // Procesar los items
                foreach ($cart->getCartProductOrders() as $cartProductOrder) {
                    $product = $cartProductOrder->getProduct();
                    $quantity = $cartProductOrder->getQuantity();

                    if (!$cartProductOrder->isFromReservation()) {
                        if ($product->getQuantity() < $quantity) {
                            throw new \Exception('No hay suficiente stock para ' . $product->getName());
                        }

                        $previousStock = $product->getQuantity();
                        $newStock = $previousStock - $quantity;

                        $this->productMovementService->recordMovement(
                            $product,
                            ProductMovement::TYPE_SALE,
                            $quantity,
                            $previousStock,
                            $newStock,
                            sprintf('Venta realizada desde carrito. Transacción: %s', $purchase->getTransactionId())
                        );

                        $product->setQuantity($newStock);
                        $entityManager->persist($product);
                    } else {
                        // Para productos de reserva, registrar un movimiento especial
                        $this->productMovementService->recordMovement(
                            $product,
                            ProductMovement::TYPE_RESERVED_SALE,
                            $quantity,
                            $product->getQuantity(),
                            $product->getQuantity(), // No cambia el stock pero registra la venta
                            sprintf('Venta de producto reservado. Transacción: %s', $purchase->getTransactionId())
                        );
                    }

                    $purchaseItem = new PurchaseItem();
                    $purchaseItem->setProduct($product);
                    $purchaseItem->setQuantity($quantity);
                    $purchaseItem->setPrice($product->getPrice());
                    $purchaseItem->setPurchase($purchase);
                    $entityManager->persist($purchaseItem);
                }

                // Configurar detalles de pago
                $dateArgentina = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
                $purchase->setPaymentDetails([
                    'card_last_four' => substr($data['card_number'], -4),
                    'payment_method' => 'credit_card',
                    'card_type' => $this->getCardType($data['card_number']),
                    'payment_date' => $dateArgentina->format('Y-m-d H:i:s'),
                    'created_by' => 'SantiAragon'
                ]);

                // Limpiar el carrito
                foreach ($cart->getCartProductOrders() as $order) {
                    $entityManager->remove($order);
                }

            } else {
                // Procesar compra individual
                $product = $entityManager->getRepository(Product::class)->find($data['product_id']);
                if (!$product) {
                    throw new \Exception('Producto no encontrado');
                }

                $quantity = (int) $data['quantity'];
                if ($product->getQuantity() < $quantity) {
                    throw new \Exception('No hay suficiente stock disponible');
                }

                $total = $product->getPrice() * $quantity;

                // Crear y configurar la compra
                $purchase = new Purchase();
                $purchase->setUser($this->getUser());
                $purchase->setStatus('completed');
                $purchase->setTransactionId(uniqid('TRX-'));
                $purchase->setTotalPrice($total);
                $purchase->setPurchaseDate(new DateTime('now'));

                $entityManager->persist($purchase);
                $entityManager->flush();

                // Registrar el movimiento
                $previousStock = $product->getQuantity();
                $newStock = $previousStock - $quantity;
                
                $this->productMovementService->recordMovement(
                    $product,
                    ProductMovement::TYPE_SALE,
                    $quantity,
                    $previousStock,
                    $newStock,
                    sprintf('Venta individual realizada. Transacción: %s', $purchase->getTransactionId())
                );

                // Actualizar stock
                $product->setQuantity($newStock);
                $entityManager->persist($product);

                // Crear y persistir PurchaseItem
                $purchaseItem = new PurchaseItem();
                $purchaseItem->setProduct($product);
                $purchaseItem->setQuantity($quantity);
                $purchaseItem->setPrice($product->getPrice());
                $purchaseItem->setPurchase($purchase);
                $entityManager->persist($purchaseItem);

                // Configurar detalles de pago
                $dateArgentina = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
                $purchase->setPaymentDetails([
                    'card_last_four' => substr($data['card_number'], -4),
                    'payment_method' => 'credit_card',
                    'card_type' => $this->getCardType($data['card_number']),
                    'payment_date' => $dateArgentina->format('Y-m-d H:i:s'),
                    'created_by' => 'SantiAragon'
                ]);
            }

            // Flush final para guardar todos los cambios
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'redirect' => $this->generateUrl('purchase_success', [
                    'transactionId' => $purchase->getTransactionId()
                ])
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'errors' => [$e->getMessage()]
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function getCardType(string $cardNumber): string
    {
        $patterns = [
            'visa' => '/^4[0-9]{12}(?:[0-9]{3})?$/',
            'mastercard' => '/^5[1-5][0-9]{14}$/',
            'amex' => '/^3[47][0-9]{13}$/',
            'discover' => '/^6(?:011|5[0-9]{2})[0-9]{12}$/'
        ];

        $cardNumber = preg_replace('/\D/', '', $cardNumber);

        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $cardNumber)) {
                return ucfirst($type);
            }
        }

        return 'Unknown';
    }

    #[Route('/purchase/success/{transactionId}', name: 'purchase_success')]
    public function purchaseSuccess(
        string $transactionId,
        EntityManagerInterface $entityManager
    ): Response {
        $purchase = $entityManager->getRepository(Purchase::class)
            ->findOneBy(['transactionId' => $transactionId]);

        if (!$purchase) {
            throw $this->createNotFoundException('Compra no encontrada');
        }

        // Verificar que el usuario actual es el propietario de la compra
        if ($purchase->getUser() !== $this->security->getUser()) {
            throw $this->createAccessDeniedException('No tienes permiso para ver esta compra');
        }

        return $this->render('purchase/success.html.twig', [
            'purchase' => $purchase
        ]);
    }

    private function validateCardData(array $data): array
    {
        $errors = [];

        if (!isset($data['card_number'], $data['expiry_month'], $data['expiry_year'], $data['cvv'])) {
            return ['error' => 'Todos los campos son requeridos'];
        }

        $cardNumber = preg_replace('/\D/', '', $data['card_number']);
        $expiryMonth = (int) $data['expiry_month'];
        $expiryYear = (int) $data['expiry_year'];
        $cvv = preg_replace('/\D/', '', $data['cvv']);

        if (strlen($cardNumber) !== 16) {
            $errors[] = 'El número de tarjeta debe tener 16 dígitos';
        }

        if (!preg_match('/^(4|5|6|37)/', $cardNumber)) {
            $errors[] = 'Número de tarjeta inválido';
        }

        if ($expiryMonth < 1 || $expiryMonth > 12) {
            $errors[] = 'Mes de vencimiento inválido';
        }

        if ($expiryYear < date('Y')) {
            $errors[] = 'La tarjeta está vencida';
        }

        if ($expiryYear == date('Y') && $expiryMonth < date('n')) {
            $errors[] = 'La tarjeta está vencida';
        }

        if (strlen($cvv) !== 3) {
            $errors[] = 'El CVV debe tener 3 dígitos';
        }

        return $errors;
    }

}