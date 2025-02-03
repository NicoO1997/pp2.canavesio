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

class ProductController extends AbstractController
{
    #[Route('/product/new', name: 'new_product')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Manejar la subida de la imagen
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                // Convertir el contenido del archivo a base64
                $imageData = base64_encode(file_get_contents($imageFile->getPathname()));
                $product->setImage($imageData);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_list');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/product', name: 'product_list')]
    public function list(
        EntityManagerInterface $entityManager,
        UserFavoriteProductRepository $userFavoriteProductRepository
    ): Response {
        $user = $this->getUser();
        $products = $entityManager->getRepository(Product::class)->findAll();

        // Obtener IDs de productos favoritos del usuario actual
        $favoriteProductIds = [];
        if ($user) {
            $favorites = $userFavoriteProductRepository->findBy(['user' => $user]);
            $favoriteProductIds = array_map(function ($favorite) {
                return $favorite->getProduct()->getId();
            }, $favorites);
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

        // Obtener IDs de productos favoritos del usuario actual
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

        // Verificar si el producto ya está en favoritos
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
}
