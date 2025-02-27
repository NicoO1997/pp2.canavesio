<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\UserFavoriteProduct;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\UserFavoriteProductRepository;
use App\Entity\Favorite;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductController extends AbstractController
{
    #[Route('/product/new', name: 'new_product')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $product->setIsEnabled(false);
        
        if (!$this->isGranted('ROLE_GESTORSTOCK')) {
            $product->setMinStock(0);
        }
        
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $imageData = base64_encode(file_get_contents($imageFile->getPathname()));
                $product->setImage($imageData);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('view_stock');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/stock', name: 'view_stock')]
    public function viewStock(
        EntityManagerInterface $entityManager,
        UserFavoriteProductRepository $userFavoriteProductRepository
    ): Response {
        $user = $this->getUser();
        
        $products = $entityManager->getRepository(Product::class)->findAll();

        $favoriteProductIds = [];
        if ($user) {
            $favorites = $userFavoriteProductRepository->findBy(['user' => $user]);
            $favoriteProductIds = array_map(fn($favorite) => $favorite->getProduct()->getId(), $favorites);
        }

        return $this->render('product/view_stock.html.twig', [
            'products' => $products,
            'favoriteProductIds' => $favoriteProductIds,
        ]);
    }

    #[Route('/product', name: 'product_list')]
    public function list(
        EntityManagerInterface $entityManager,
        UserFavoriteProductRepository $userFavoriteProductRepository
    ): Response {
        $user = $this->getUser();
        
        $products = $entityManager->getRepository(Product::class)->findEnabledProducts();

        $favoriteProductIds = [];
        if ($user) {
            $favorites = $userFavoriteProductRepository->findBy(['user' => $user]);
            $favoriteProductIds = array_map(fn($favorite) => $favorite->getProduct()->getId(), $favorites);
        }

        return $this->render('product/list.html.twig', [
            'products' => $products,
            'favoriteProductIds' => $favoriteProductIds,
        ]);
    }


    #[Route('/product/{id}', name: 'product_detail')]
    public function detail(
        int $id,
        EntityManagerInterface $entityManager,
        UserFavoriteProductRepository $userFavoriteProductRepository
    ): Response {
        $user = $this->getUser();
        $product = $entityManager->getRepository(Product::class)->find($id);
        dump($product);

        if (!$product) {
            throw $this->createNotFoundException('El producto no existe');
        }

        $favoriteProductIds = [];
        if ($user) {
            $favorites = $userFavoriteProductRepository->findBy(['user' => $user]);
            $favoriteProductIds = array_map(function ($favorite) {
                return $favorite->getProduct()->getId();
            }, $favorites);
        }

        return $this->render('product/detail.html.twig', [
            'product' => $product,
            'favoriteProductIds' => $favoriteProductIds,
        ]);
    }

    #[Route('/product/toggle-status/{id}', name: 'product_toggle_status', methods: ['POST'])]
    public function toggleStatus(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('edit' . $product->getId(), $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF inválido'], 400);
        }
    
        try {
            $isEnabled = $request->request->get('isEnabled') === '1';
            $product->setIsEnabled($isEnabled);
    
            $entityManager->persist($product);
            $entityManager->flush();
    
            return new JsonResponse([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'newStatus' => $isEnabled
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error al actualizar el estado del producto'
            ], 500);
        }
    }

    #[Route('/product/{id}/favorite', name: 'add_favorite', methods: ['POST'])]
    public function addFavorite(
        int $id,
        ProductRepository $productRepository,
        UserFavoriteProductRepository $userFavoriteProductRepository,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $product = $productRepository->find($id);
        if (!$product) {
            return $this->redirectToRoute('product_list');
        }

        $existingFavorite = $userFavoriteProductRepository->findOneBy([
            'user' => $user,
            'product' => $product,
        ]);

        if ($existingFavorite) {
            $this->addFlash('warning', 'Este producto ya se encuentra en favoritos.');
            return $this->redirectToRoute('product_list');
        }

        $favorite = new Favorite();
        $favorite->setFlag(true);

        $userFavoriteProduct = new UserFavoriteProduct();
        $userFavoriteProduct->setUser($user);
        $userFavoriteProduct->setProduct($product);
        $userFavoriteProduct->setFavorite($favorite);

        $entityManager->persist($favorite);
        $entityManager->persist($userFavoriteProduct);
        $entityManager->flush();

        $this->addFlash('success', 'Producto agregado a favoritos.');
        return $this->redirectToRoute('product_list');
    }

    #[Route('/product/{id}/favorite/remove', name: 'remove_favorite', methods: ['POST'])]
    public function removeFromFavorites(
        int $id,
        EntityManagerInterface $entityManager,
        UserFavoriteProductRepository $userFavoriteProductRepository
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('El producto no existe');
        }

        $favorite = $userFavoriteProductRepository->findOneBy([
            'user' => $user,
            'product' => $product
        ]);

        if ($favorite) {
            $entityManager->remove($favorite);
            $entityManager->flush();

            $this->addFlash('success', 'Producto eliminado de favoritos');
        }

        return $this->redirectToRoute('product_detail', ['id' => $id]);
    }

    #[Route('/product/edit/{id}', name: 'product_edit', methods: ['POST'])]
    public function editProduct(Request $request, EntityManagerInterface $entityManager, Product $product): JsonResponse
    {
        dump($request->request->all());
        $csrfToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('edit' . $product->getId(), $csrfToken)) {
            return new JsonResponse(['message' => 'Token CSRF inválido.'], 400);
        }

        if ($request->request->has('isEnabled')) {
            $isEnabled = $request->request->get('isEnabled') === '1' ? true : false;
            $product->setIsEnabled($isEnabled);
        } else {
            dump('⚠ No llegó isEnabled');
        }

        if ($request->request->has('name')) {
            $product->setName($request->request->get('name'));
        }
        
        if ($request->request->has('brand')) {
            $product->setBrand($request->request->get('brand'));
        }
        
        if ($request->request->has('quantity')) {
            $product->setQuantity((int)$request->request->get('quantity'));
        }
        
        if ($request->request->has('minStock')) {
            $product->setMinStock((int)$request->request->get('minStock'));
        }
        
        if ($request->request->has('price')) {
            $product->setPrice((float)$request->request->get('price'));
        }
        
        if ($request->request->has('description')) {
            $product->setDescription($request->request->get('description'));
        }

        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Producto actualizado correctamente.']);
    }

    #[Route('/product/{id}/delete', name: 'product_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Product $product,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', 'Producto eliminado correctamente');
        } else {
            $this->addFlash('error', 'Token CSRF inválido');
        }

        return $this->redirectToRoute('view_stock');
    }
}