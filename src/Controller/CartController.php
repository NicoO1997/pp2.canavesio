<?php
namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProductOrder;
use App\Entity\Product;
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
            $cartProductOrder->setQuantity($cartProductOrder->getQuantity() + $quantity);
        } else {
            $cartProductOrder = new CartProductOrder();
            $cartProductOrder->setProduct($product);
            $cartProductOrder->setCart($cart);
            $cartProductOrder->setQuantity($quantity);
            $cartProductOrder->setDate(new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires')));
            $cartProductOrder->setTime(new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires')));

            $entityManager->persist($cartProductOrder);
        }

        $product->setQuantity($product->getQuantity() - $quantity);
        $entityManager->persist($product);

        $this->productMovementService->recordMovement(
            $product,
            ProductMovement::TYPE_SALE,
            $quantity,
            'Venta a través del carrito'
        );

        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Producto agregado al carrito.']);
    }

    #[Route('/cart', name: 'cart_view')]
    public function viewCart(CartRepository $cartRepository, Security $security): Response {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cart = $cartRepository->findOneBy(['user' => $user]);

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

        $cart->removeCartProductOrder($cartProductOrder);
        
        $product = $cartProductOrder->getProduct();
        if ($product) {
            $quantity = $cartProductOrder->getQuantity();
            $product->setQuantity($product->getQuantity() + $quantity);
            $entityManager->persist($product);

            $this->productMovementService->recordMovement(
                $product,
                ProductMovement::TYPE_ENTRY,
                $quantity,
                'Devolución al stock por eliminación del carrito'
            );
        }

        $entityManager->remove($cartProductOrder);
        $entityManager->flush();

        $this->addFlash('success', 'Producto eliminado del carrito');

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
        $quantity = (int) $request->request->get('quantity');

        if ($quantity < 1) {
            return new JsonResponse(['success' => false, 'message' => 'La cantidad debe ser al menos 1.'], 400);
        }

        $cartProductOrder = $entityManager->getRepository(CartProductOrder::class)->find($id);

        if (!$cartProductOrder) {
            return new JsonResponse(['success' => false, 'message' => 'Producto no encontrado en el carrito.'], 404);
        }

        $cart = $cartProductOrder->getCart();
        if ($cart->getUser() !== $user) {
            return new JsonResponse(['success' => false, 'message' => 'No tienes permiso para modificar este carrito.'], 403);
        }

        $product = $cartProductOrder->getProduct();
        $quantityDiff = $quantity - $cartProductOrder->getQuantity();

        if ($quantityDiff > 0 && $product->getQuantity() < $quantityDiff) {
            return new JsonResponse([
                'success' => false, 
                'message' => 'No hay suficiente stock disponible.'
            ], 400);
        }

        $cartProductOrder->setQuantity($quantity);
        $product->setQuantity($product->getQuantity() - $quantityDiff);

        if ($quantityDiff !== 0) {
            $this->productMovementService->recordMovement(
                $product,
                $quantityDiff > 0 ? ProductMovement::TYPE_SALE : ProductMovement::TYPE_ENTRY,
                abs($quantityDiff),
                $quantityDiff > 0 ? 'Aumento de cantidad en carrito' : 'Disminución de cantidad en carrito'
            );
        }

        $entityManager->persist($cartProductOrder);
        $entityManager->persist($product);
        $entityManager->flush();

        $subtotal = $product->getPrice() * $quantity;

        $total = 0;
        foreach ($cart->getCartProductOrders() as $item) {
            $total += $item->getProduct()->getPrice() * $item->getQuantity();
        }

        return new JsonResponse([
            'success' => true, 
            'message' => 'Cantidad actualizada.',
            'subtotal' => $subtotal,
            'total' => $total
        ]);
    }
}