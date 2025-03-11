<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\Cart;
use App\Entity\CartProductOrder;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    #[Route('/reservations/create', name: 'reservation_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_VENDEDOR')) {
            $this->addFlash('error', 'No tienes permisos para realizar reservas.');
            return $this->redirectToRoute('app_login');
        }

        $products = $entityManager->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->where('p.isEnabled = :enabled')
            ->andWhere('p.quantity > 0')
            ->setParameter('enabled', true)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();

        $users = $entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_USUARIO"%')
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->getResult();

        $reservations = $entityManager->getRepository(Reservation::class)
            ->findBy(
                ['seller' => $this->getUser()],
                ['createdAt' => 'DESC']
            );

        return $this->render('reservation/create.html.twig', [
            'products' => $products,
            'users' => $users,
            'reservations' => $reservations
        ]);
    }

    #[Route('/reservations/store', name: 'reservation_store', methods: ['POST'])]
    public function store(
        Request $request, 
        EntityManagerInterface $entityManager,
        \App\Service\ProductMovementService $productMovementService
    ): Response {
        if (!$this->isGranted('ROLE_VENDEDOR')) {
            $this->addFlash('error', 'No tienes permisos para realizar reservas.');
            return $this->redirectToRoute('app_login');
        }

        $userId = $request->request->get('user');
        $productIds = $request->request->all('products');
        $quantities = $request->request->all('quantities');

        if (empty($productIds) || empty($quantities)) {
            $this->addFlash('error', 'Debe seleccionar al menos un producto y su cantidad.');
            return $this->redirectToRoute('reservation_create');
        }

        $user = $entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            $this->addFlash('error', 'Usuario no encontrado.');
            return $this->redirectToRoute('reservation_create');
        }

        $now = new \DateTime('2025-03-05 15:28:46');
        $success = true;

        foreach ($productIds as $index => $productId) {
            if (!isset($quantities[$index])) {
                continue;
            }

            $product = $entityManager->getRepository(Product::class)->find($productId);
            $quantity = (int)$quantities[$index];

            if (!$product) {
                $this->addFlash('error', 'Uno de los productos seleccionados no fue encontrado.');
                continue;
            }

            if ($quantity <= 0) {
                $this->addFlash('error', sprintf(
                    'La cantidad para %s debe ser mayor a 0.',
                    $product->getName()
                ));
                $success = false;
                continue;
            }

            if ($quantity > $product->getQuantity()) {
                $this->addFlash('error', sprintf(
                    'No hay suficiente stock disponible para %s (Stock actual: %d).',
                    $product->getName(),
                    $product->getQuantity()
                ));
                $success = false;
                continue;
            }

            // Usar el método específico para reservas que maneja todo el proceso
            $productMovementService->recordReservation(
                $product,
                $quantity,
                sprintf('Reserva de %d unidades para %s', $quantity, $user->getEmail())
            );
            // Ya no actualizamos el producto aquí, pues lo hace recordReservedSale

            $reservation = new Reservation();
            $reservation->setProduct($product);
            $reservation->setCustomer($user);
            $reservation->setSeller($this->getUser());
            $reservation->setQuantity($quantity);
            $reservation->setStatus('pending');
            $reservation->setCreatedBy('NicoO1997');
            $reservation->setCreatedAt($now);
            $reservation->setExpiresAt((clone $now)->modify('+48 hours'));

            $entityManager->persist($reservation);
        }

        if ($success) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Reservas creadas exitosamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Ocurrió un error al crear las reservas: ' . $e->getMessage());
                return $this->redirectToRoute('reservation_create');
            }
        }

        return $this->redirectToRoute('reservation_create');
    }

    #[Route('/reservations', name: 'reservation_list')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->isGranted('ROLE_USUARIO')) {
            $reservations = $entityManager->getRepository(Reservation::class)
                ->findBy(
                    ['customer' => $user],
                    ['createdAt' => 'DESC']
                );

            return $this->render('reservation/list.html.twig', [
                'reservations' => $reservations
            ]);
        }

        return $this->redirectToRoute('reservation_create');
    }

    #[Route('/reservation/{id}/cancel', name: 'reservation_cancel', methods: ['POST'])]
    public function cancel(
        Reservation $reservation,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$this->isGranted('ROLE_VENDEDOR') && $reservation->getCustomer() !== $user) {
            $this->addFlash('error', 'No tienes permisos para cancelar esta reserva.');
            return $this->redirectToRoute('reservation_list');
        }

        if ($reservation->getStatus() === 'pending') {
            $product = $reservation->getProduct();
            $product->setQuantity($product->getQuantity() + $reservation->getQuantity());
            
            $reservation->setStatus('cancelled');
            $reservation->setUpdatedAt(new \DateTime('2025-03-05 15:28:46'));
            $reservation->setUpdatedBy('NicoO1997');

            $entityManager->flush();

            $this->addFlash('success', 'Reserva cancelada exitosamente.');
        } else {
            $this->addFlash('error', 'Esta reserva no puede ser cancelada.');
        }

        if ($this->isGranted('ROLE_VENDEDOR')) {
            return $this->redirectToRoute('reservation_create');
        }
        
        return $this->redirectToRoute('reservation_list');
    }

    /**
     * Marca una reserva como entregada físicamente
     */
    #[Route('/reservation/{id}/deliver', name: 'reservation_deliver', methods: ['POST'])]
    public function deliver(
        Reservation $reservation,
        EntityManagerInterface $entityManager,
        \App\Service\ProductMovementService $productMovementService
    ): Response {
        if (!$this->isGranted('ROLE_VENDEDOR')) {
            $this->addFlash('error', 'No tienes permisos para marcar entregas.');
            return $this->redirectToRoute('app_login');
        }

        if ($reservation->getStatus() !== 'pending') {
            $this->addFlash('error', 'Solo se pueden finalizar compras de reservas en estado pendiente.');
            return $this->redirectToRoute('reservation_create');
        }
        
        try {
            // Registrar la entrega en el historial de movimientos
            $productMovementService->recordPhysicalDelivery(
                $reservation->getProduct(),
                $reservation->getQuantity(),
                sprintf('Entrega física de reserva a %s', $reservation->getCustomer()->getEmail())
            );
            
            // Actualizar el estado de la reserva a "delivered"
            $reservation->setStatus('delivered');
            $reservation->setUpdatedAt(new \DateTime('2025-03-11 13:34:09')); // Fecha actual proporcionada
            $reservation->setUpdatedBy('NicoO1997'); // Usuario actual proporcionado
            
            $entityManager->flush();
            $this->addFlash('success', 'Producto entregado exitosamente.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Ocurrió un error al registrar la entrega: ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('reservation_create');
    }

    #[Route('/reservation/{id}/add-to-cart', name: 'cart_add_reservation', methods: ['POST'])]
    public function addToCart(
        Reservation $reservation,
        EntityManagerInterface $entityManager,
        CartRepository $cartRepository,
        \App\Service\ProductMovementService $productMovementService
    ): JsonResponse {
        $user = $this->getUser();
        
        if (!$user || !$this->isGranted('ROLE_USUARIO')) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.'
            ], 403);
        }

        if ($reservation->getCustomer() !== $user) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No puedes agregar al carrito una reserva que no te pertenece.'
            ], 403);
        }

        if ($reservation->getStatus() !== 'pending') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Solo se pueden agregar al carrito reservas pendientes.'
            ], 400);
        }

        $cart = $cartRepository->findOneBy(['user' => $user]);
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $entityManager->persist($cart);
        }

        $product = $reservation->getProduct();
        $quantity = $reservation->getQuantity();

        // Registrar la finalización de la reserva en el historial de movimientos
        // usando un tipo especial de movimiento que no modifica el stock
        // ya que éste ya fue descontado en la reserva inicial
        $productMovementService->recordMovement(
            $product,
            'reserved_sale', // Mismo tipo que en la reserva
            0, // Cantidad 0 porque ya fue descontada
            $product->getQuantity(), // El stock actual
            $product->getQuantity(), // El stock no cambia
            // Opción 2: Usar el método getUserIdentifier() que parece estar disponible
            sprintf('Finalización de compra de reserva a %s', $user->getUserIdentifier()),
            $user->getUserIdentifier()
        );

        $cartProductOrder = $cart->getCartProductOrders()
            ->filter(fn($cpo) => $cpo->getProduct() === $product)
            ->first();

        if ($cartProductOrder) {
            $cartProductOrder->setQuantity($cartProductOrder->getQuantity() + $quantity);
        } else {
            $cartProductOrder = new CartProductOrder();
            $cartProductOrder->setProduct($product);
            $cartProductOrder->setCart($cart);
            $cartProductOrder->setQuantity($quantity);
            $cartProductOrder->setIsFromReservation(true);
            $cartProductOrder->setDate(new \DateTime('2025-03-11 13:34:09'));
            $cartProductOrder->setTime(new \DateTime('2025-03-11 13:34:09'));

            $entityManager->persist($cartProductOrder);
        }

        $reservation->setStatus('completed');
        $reservation->setUpdatedAt(new \DateTime('2025-03-11 13:34:09'));
        $reservation->setUpdatedBy($user->getUserIdentifier());

        try {
            $entityManager->flush();
            return new JsonResponse([
                'success' => true,
                'message' => 'Reserva agregada al carrito exitosamente.'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Ocurrió un error al agregar la reserva al carrito: ' . $e->getMessage()
            ], 500);
        }
    }
}