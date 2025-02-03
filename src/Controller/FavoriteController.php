<?php
namespace App\Controller;

use App\Entity\Favorite;
use App\Entity\UserFavoriteProduct;
use App\Repository\ProductRepository;
use App\Repository\UserFavoriteProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class FavoriteController extends AbstractController
{
    #[Route('/favorite/add/{productId}', name: 'add_favorite', methods: ['POST'])]
    public function addFavorite(
        int $productId,
        ProductRepository $productRepository,
        UserFavoriteProductRepository $userFavoriteProductRepository,
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

    #[Route('/favorite', name: 'favorite_list', methods: ['GET'])]
    public function listFavorites(UserFavoriteProductRepository $userFavoriteProductRepository, Security $security): Response {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $favorites = $userFavoriteProductRepository->findBy(['user' => $user]);

        // Obtener los IDs de los productos que ya están en favoritos
        $favoriteProductIds = array_map(function($favorite) {
            return $favorite->getProduct()->getId();
        }, $favorites);

        return $this->render('favorite/list.html.twig', [
            'favorites' => $favorites,
            'favoriteProductIds' => $favoriteProductIds, // Pasar los IDs a la plantilla
        ]);
    }

    #[Route('/favorite/remove/{id}', name: 'remove_favorite', methods: ['POST'])]
    public function removeFavorite(
        int $id,
        UserFavoriteProductRepository $userFavoriteProductRepository,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $userFavoriteProduct = $userFavoriteProductRepository->find($id);
        if (!$userFavoriteProduct || $userFavoriteProduct->getUser() !== $user) {
            throw $this->createNotFoundException('Favorite not found or you do not have access to this favorite.');
        }

        $favorite = $userFavoriteProduct->getFavorite();
        $entityManager->remove($userFavoriteProduct);
        $entityManager->flush();

        // Check if the favorite entity has no more associated UserFavoriteProduct
        if ($favorite->getUserFavoriteProduct()->isEmpty()) {
            $entityManager->remove($favorite);
            $entityManager->flush();
        }

        return $this->redirectToRoute('favorite_list');
    }
}
