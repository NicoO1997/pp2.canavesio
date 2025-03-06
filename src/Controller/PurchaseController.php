<?php
// src/Controller/PurchaseController.php

namespace App\Controller;

use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security; // Cambio aquí: nueva ubicación
use App\Entity\Cart;
use DateTimeZone;
class PurchaseController extends AbstractController
{
    public function __construct(
        private Security $security
    ) {
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

    #[Route('/purchase/simulate-cart', name: 'purchase_simulate_cart')]
    public function simulateCartPayment(
        EntityManagerInterface $entityManager
    ): Response {
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
            // Crear una nueva compra para todos los productos del carrito
            $purchase = new Purchase();
            $purchase->setUser($user);

            $total = 0;
            $purchaseItems = []; // Array para almacenar los items de la compra

            foreach ($cart->getCartProductOrders() as $cartProductOrder) {
                $product = $cartProductOrder->getProduct();
                $quantity = $cartProductOrder->getQuantity();

                // Verificar que la cantidad sea válida
                if (!$quantity || $quantity <= 0) {
                    throw new \InvalidArgumentException('La cantidad debe ser mayor a 0 para ' . $product->getName());
                }

                // Verificar stock disponible
                if ($product->getQuantity() < $quantity) {
                    $this->addFlash('error', 'No hay suficiente stock para ' . $product->getName());
                    return $this->redirectToRoute('cart_view');
                }

                // Crear PurchaseItem
                $purchaseItem = new PurchaseItem();
                $purchaseItem->setProduct($product);
                $purchaseItem->setQuantity($quantity);
                $purchaseItem->setPrice($product->getPrice());
                $purchaseItem->setPurchase($purchase);

                $purchaseItems[] = $purchaseItem;

                // Actualizar el total
                $total += $product->getPrice() * $quantity;

                // Actualizar el stock del producto
                $product->setQuantity($product->getQuantity() - $quantity);
                $entityManager->persist($product);
            }

            // Configurar la compra
            $purchase->setTotalPrice($total);
            $purchase->setStatus('completed');
            $purchase->setTransactionId(uniqid('TRX-'));
            $purchase->setPurchaseDate(new \DateTime());

            // Guardar los detalles de la compra
            $dateArgentina = new \DateTime('now', new \DateTimeZone('America/Argentina/Buenos_Aires'));
            $cartItems = [];

            foreach ($cart->getCartProductOrders() as $order) {
                if ($order->getQuantity() > 0) {
                    $cartItems[] = [
                        'product_id' => $order->getProduct()->getId(),
                        'product_name' => $order->getProduct()->getName(),
                        'quantity' => $order->getQuantity(),
                        'price' => $order->getProduct()->getPrice()
                    ];
                }
            }

            $purchase->setPaymentDetails([
                'payment_method' => 'simulated_payment',
                'payment_date' => $dateArgentina->format('Y-m-d H:i:s'),
                'cart_items' => $cartItems
            ]);

            // Persistir la compra y sus items
            $entityManager->persist($purchase);
            foreach ($purchaseItems as $item) {
                $entityManager->persist($item);
            }

            // Limpiar el carrito
            foreach ($cart->getCartProductOrders() as $order) {
                $entityManager->remove($order);
            }

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

                // Crear una nueva compra
                $purchase = new Purchase();
                $purchase->setUser($this->getUser());
                $purchase->setStatus('completed');
                $purchase->setTransactionId(uniqid('TRX-'));

                $total = 0;

                foreach ($cart->getCartProductOrders() as $cartProductOrder) {
                    $product = $cartProductOrder->getProduct();
                    $quantity = $cartProductOrder->getQuantity();

                    // Verificar stock
                    if ($product->getQuantity() < $quantity) {
                        throw new \Exception('No hay suficiente stock para ' . $product->getName());
                    }

                    // Crear PurchaseItem
                    $purchaseItem = new PurchaseItem();
                    $purchaseItem->setProduct($product);
                    $purchaseItem->setQuantity($quantity);
                    $purchaseItem->setPrice($product->getPrice());
                    $purchaseItem->setPurchase($purchase);

                    // Actualizar stock
                    $product->setQuantity($product->getQuantity() - $quantity);
                    $entityManager->persist($product);

                    $total += $product->getPrice() * $quantity;
                    $entityManager->persist($purchaseItem);
                }

                $purchase->setTotalPrice($total);

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

                // Crear compra individual
                $purchase = new Purchase();
                $purchase->setUser($this->getUser());
                $purchase->setStatus('completed');
                $purchase->setTransactionId(uniqid('TRX-'));
                $purchase->setTotalPrice($product->getPrice() * $quantity);

                // Crear PurchaseItem para compra individual
                $purchaseItem = new PurchaseItem();
                $purchaseItem->setProduct($product);
                $purchaseItem->setQuantity($quantity);
                $purchaseItem->setPrice($product->getPrice());
                $purchaseItem->setPurchase($purchase);

                // Actualizar stock
                $product->setQuantity($product->getQuantity() - $quantity);

                $entityManager->persist($product);
                $entityManager->persist($purchaseItem);
            }

            // Configurar detalles de pago comunes
            $dateArgentina = new \DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
            $purchase->setPaymentDetails([
                'card_last_four' => substr($data['card_number'], -4),
                'payment_method' => 'credit_card',
                'card_type' => $this->getCardType($data['card_number']),
                'payment_date' => $dateArgentina->format('Y-m-d H:i:s')
            ]);

            $entityManager->persist($purchase);

            // Si es compra de carrito, limpiar el carrito
            if ($isCartPurchase && isset($cart)) {
                foreach ($cart->getCartProductOrders() as $order) {
                    $entityManager->remove($order);
                }
            }

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