<?php
namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProductOrder;
use App\Entity\Product;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

use Symfony\Component\Security\Csrf\CsrfToken;

class CartController extends AbstractController
{
    #[Route('/cart/add/{productId}', name: 'cart_add_product', methods: ['POST'])]
    public function addProduct(
        int $productId,
        Request $request,
        ProductRepository $productRepository,
        CartRepository $cartRepository,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
    
        $product = $productRepository->find($productId);
        if (!$product) {
            return $this->redirectToRoute('product_list');
        }
    
        $quantity = (int) $request->request->get('quantity');
        if ($quantity < 1 || $quantity > $product->getQuantity()) {
            // Handle invalid quantity (optional: add an error message or redirect)
            return $this->redirectToRoute('product_list');
        }
    
        // Find or create a cart for the user
        $cart = $cartRepository->findOneBy(['user' => $user]);
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $entityManager->persist($cart);
            $entityManager->flush();
        }
    
        // Check if product is already in the cart
        $cartProductOrder = $cart->getCartProductOrders()->filter(function ($cartProductOrder) use ($product) {
            return $cartProductOrder->getProduct() === $product;
        })->first();
    
        if ($cartProductOrder) {
            // Increment quantity if product is already in the cart
            $cartProductOrder->setQuantity($cartProductOrder->getQuantity() + $quantity);
        } else {
            // Add new product to the cart
            $cartProductOrder = new CartProductOrder();
            $cartProductOrder->setProduct($product);
            $cartProductOrder->setCart($cart);
            $cartProductOrder->setQuantity($quantity);
            $cartProductOrder->setDate(new \DateTime());
            $cartProductOrder->setTime(new \DateTime());
    
            $entityManager->persist($cartProductOrder);
        }
    
        // Update the stock quantity
        $product->setQuantity($product->getQuantity() - $quantity);
        $entityManager->persist($product);
    
        $entityManager->flush();
    
        return $this->redirectToRoute('cart_view');
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
    
        // Eliminar completamente el producto del carrito
        $cart->removeCartProductOrder($cartProductOrder);
        $entityManager->remove($cartProductOrder);
    
        // Opcional: Devolver la cantidad al stock del producto
        $product = $cartProductOrder->getProduct();
        if ($product) {
            $product->setQuantity($product->getQuantity() + $cartProductOrder->getQuantity());
            $entityManager->persist($product);
        }
    
        $entityManager->flush();
    
        // Añadir un mensaje flash para confirmar la eliminación
        $this->addFlash('success', 'Producto eliminado del carrito');
    
        return $this->redirectToRoute('cart_view');
    }
}