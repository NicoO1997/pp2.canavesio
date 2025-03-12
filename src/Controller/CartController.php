<?php
namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProductOrder;
use App\Entity\Product;
use App\Entity\Reservation;
use App\Entity\ProductMovement;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductRepository;
use App\Service\ProductMovementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use DateTime;
use DateTimeZone;

class CartController extends AbstractController
{
    private $productMovementService;

    public function __construct(ProductMovementService $productMovementService)
    {
        $this->productMovementService = $productMovementService;
    }

    #[Route('/cart/add/{productId}', name: 'cart_add_product', methods: ['POST'])]
    public function addProduct(
        int $productId,
        Request $request,
        ProductRepository $productRepository,
        CartRepository $cartRepository,
        EntityManagerInterface $entityManager,
        Security $security
    ): JsonResponse {
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Debes iniciar sesión.'], 403);
        }

        $product = $productRepository->find($productId);
        if (!$product) {
            return new JsonResponse(['success' => false, 'message' => 'Producto no encontrado.'], 404);
        }

        $quantity = (int) $request->request->get('quantity');
        if ($quantity < 1 || $quantity > $product->getQuantity()) {
            return new JsonResponse(['success' => false, 'message' => 'Cantidad inválida.'], 400);
        }

        $cart = $cartRepository->findOneBy(['user' => $user]);
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $entityManager->persist($cart);
            $entityManager->flush();
        }

        $cartProductOrder = $cart->getCartProductOrders()->filter(fn($cpo) => $cpo->getProduct() === $product)->first();

        if ($cartProductOrder) {
            // Verificar que la nueva cantidad total no exceda el stock disponible
            $newTotalQuantity = $cartProductOrder->getQuantity() + $quantity;
            if ($newTotalQuantity > $product->getQuantity()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'No hay suficiente stock disponible.'
                ], 400);
            }
            $cartProductOrder->setQuantity($newTotalQuantity);
        } else {
            $cartProductOrder = new CartProductOrder();
            $cartProductOrder->setProduct($product);
            $cartProductOrder->setCart($cart);
            $cartProductOrder->setQuantity($quantity);
            $cartProductOrder->setDate(new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires')));
            $cartProductOrder->setTime(new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires')));

            $entityManager->persist($cartProductOrder);
        }

        // Ya no modificamos la cantidad del producto aquí
        // Solo registramos el movimiento como "reservado"
        // $this->productMovementService->recordAdjustment(
        //     $product,
        //     $quantity,
        //     'Producto reservado en carrito'
        // );
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Producto agregado al carrito.']);
    }

    #[Route('/cart', name: 'cart_view')]
    public function viewCart(CartRepository $cartRepository, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
    
        $cart = $cartRepository->findOneBy(['user' => $user]);
        
        // Calcular el stock disponible para cada producto
        if ($cart) {
            foreach ($cart->getCartProductOrders() as $cartProductOrder) {
                $product = $cartProductOrder->getProduct();
                $product->totalAvailable = $product->getQuantity(); // Stock actual
            }
        }
    
        return $this->render('cart/view.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove_product', methods: ['POST'])]
    public function removeProduct(
        int $id,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
    
        $cartProductOrder = $entityManager->getRepository(CartProductOrder::class)->find($id);
    
        if (!$cartProductOrder) {
            throw $this->createNotFoundException('Cart product order not found');
        }
    
        $cart = $cartProductOrder->getCart();
        if ($cart->getUser() !== $user) {
            throw $this->createAccessDeniedException('You do not have permission to remove this item from the cart.');
        }
    
        $product = $cartProductOrder->getProduct();
        $quantity = $cartProductOrder->getQuantity();
    
        // Verificar si el producto viene de una reserva
        if ($cartProductOrder->isFromReservation()) {
            // Buscar la reserva relacionada más reciente
            $qb = $entityManager->getRepository(Reservation::class)
                ->createQueryBuilder('r')
                ->where('r.product = :product')
                ->andWhere('r.customer = :customer')
                ->andWhere('r.status = :status')
                ->andWhere('r.quantity = :quantity')
                ->orderBy('r.updatedAt', 'DESC')
                ->setMaxResults(1);
    
            $qb->setParameter('product', $product)
               ->setParameter('customer', $user)
               ->setParameter('status', 'completed')
               ->setParameter('quantity', $quantity);
    
            $reservation = $qb->getQuery()->getOneOrNullResult();
    
            if ($reservation) {
                // Restaurar la reserva a estado pendiente
                $reservation->setStatus('pending');
                $reservation->setUpdatedAt(new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires')));
                $reservation->setUpdatedBy($user->getUserIdentifier());
    
                // Registrar el movimiento de restauración de la reserva
                $this->productMovementService->recordReservationRestoration(
                    $product,
                    $quantity,
                    sprintf(
                        'Reserva restaurada - Producto eliminado del carrito por %s',
                        $user->getUserIdentifier()
                    ),
                    $user->getUserIdentifier()
                );
            } else {
                // Si no encontramos la reserva, loguear el error pero permitir la eliminación
                error_log(sprintf(
                    'No se encontró la reserva para restaurar - Product ID: %d, User: %s, Quantity: %d',
                    $product->getId(),
                    $user->getUserIdentifier(),
                    $quantity
                ));
            }
        }
    
        // Eliminar el producto del carrito
        $cart->removeCartProductOrder($cartProductOrder);
        $entityManager->remove($cartProductOrder);
        
        try {
            $entityManager->flush();
            $this->addFlash('success', 'Producto eliminado del carrito');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al eliminar el producto del carrito: ' . $e->getMessage());
        }
    
        return $this->redirectToRoute('cart_view');
    }

    #[Route('/cart/update-quantity', name: 'cart_update_quantity', methods: ['POST'])]
public function updateQuantity(
    Request $request,
    EntityManagerInterface $entityManager,
    Security $security
): JsonResponse {
    $user = $security->getUser();
    if (!$user) {
        return new JsonResponse(['success' => false, 'message' => 'Debes iniciar sesión.'], 403);
    }

    $id = (int) $request->request->get('id');
    $newQuantity = (int) $request->request->get('quantity');
    
    $cartProductOrder = $entityManager->getRepository(CartProductOrder::class)->find($id);

    if (!$cartProductOrder) {
        return new JsonResponse([
            'success' => false, 
            'message' => 'Producto no encontrado en el carrito.'
        ], 404);
    }

    // Verificación estricta para productos reservados
    if ($cartProductOrder->isFromReservation()) {
        return new JsonResponse([
            'success' => false,
            'message' => 'Este producto proviene de una reserva y no puede ser modificado.',
            'subtotal' => $cartProductOrder->getProduct()->getPrice() * $cartProductOrder->getQuantity(),
            'total' => $this->calculateCartTotal($cartProductOrder->getCart()),
            'availableStock' => $cartProductOrder->getProduct()->getQuantity(),
            'reservedQuantity' => $cartProductOrder->getQuantity(),
            'isReserved' => true
        ], 400);
    }
    
        $cart = $cartProductOrder->getCart();
        if ($cart->getUser() !== $user) {
            return new JsonResponse([
                'success' => false, 
                'message' => 'No tienes permiso para modificar este carrito.'
            ], 403);
        }
    
        $product = $cartProductOrder->getProduct();
        $currentQuantity = $cartProductOrder->getQuantity();
        $quantityDiff = $newQuantity - $currentQuantity;
    
        // Verificar si hay suficiente stock disponible
        if ($quantityDiff > 0 && $product->getQuantity() < $newQuantity) {
            return new JsonResponse([
                'success' => false, 
                'message' => 'No hay suficiente stock disponible.'
            ], 400);
        }
    
        // Actualizar la cantidad en el carrito
        $cartProductOrder->setQuantity($newQuantity);
    
        // Registrar el movimiento según si aumentó o disminuyó la cantidad
        // if ($quantityDiff > 0) {
        //     // Si aumentó la cantidad, registrar una nueva reserva por la diferencia
        //     $this->productMovementService->recordSale(
        //         $product,
        //         abs($quantityDiff),
        //         sprintf(
        //             'Disminución de cantidad reservada en carrito de %d a %d unidades',
        //             $currentQuantity,
        //             $newQuantity
        //         )
        //     );
        // } elseif ($quantityDiff < 0) {
        //     // Si disminuyó la cantidad, registrar una cancelación de reserva por la diferencia
        //     $this->productMovementService->recordSale(
        //         $product,
        //         abs($quantityDiff),
        //         sprintf(
        //             'Disminución de cantidad reservada en carrito de %d a %d unidades',
        //             $currentQuantity,
        //             $newQuantity
        //         )
        //     );
        // }
    
        $entityManager->flush();
    
        // Calcular subtotal y total
        $subtotal = $product->getPrice() * $newQuantity;
        
        $total = 0;
        foreach ($cart->getCartProductOrders() as $item) {
            $total += $item->getProduct()->getPrice() * $item->getQuantity();
        }
    
        return new JsonResponse([
            'success' => true, 
            'message' => 'Cantidad actualizada correctamente.',
            'subtotal' => $subtotal,
            'total' => $total,
            'availableStock' => $product->getQuantity(),
            'reservedQuantity' => $newQuantity
        ]);
    }

    private function calculateCartTotal(Cart $cart): float
    {
        $total = 0;
        foreach ($cart->getCartProductOrders() as $item) {
            $total += $item->getProduct()->getPrice() * $item->getQuantity();
        }
        return $total;
    }
}