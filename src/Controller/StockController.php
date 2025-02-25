<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
class StockController extends AbstractController
{
    #[Route('/stock/view', name: 'view_stock')]
    public function viewStock(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $products = $entityManager->getRepository(Product::class)->findAll();
        
        return $this->render('stock/view_stock.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/product/{id}/edit', name: 'product_edit', methods: ['POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isCsrfTokenValid('edit'.$product->getId(), $request->request->get('_token'))) {
            return new JsonResponse(['message' => 'Token CSRF invÃ¡lido'], 400);
        }

        $originalData = [
            'name' => $product->getName(),
            'brand' => $product->getBrand(),
            'quantity' => $product->getQuantity(),
            'minStock' => $product->getMinStock(),
            'price' => $product->getPrice(),
            'description' => $product->getDescription(),
        ];

        $product->setName($request->request->get('name', $product->getName()));
        $product->setBrand($request->request->get('brand', $product->getBrand()));
        $product->setQuantity((int)$request->request->get('quantity', $product->getQuantity()));
        $product->setMinStock((int)$request->request->get('minStock', $product->getMinStock()));
        $product->setPrice($request->request->get('price', $product->getPrice()));
        $product->setDescription($request->request->get('description', $product->getDescription()));

        $changes = false;
        foreach ($originalData as $field => $value) {
            $getter = 'get'.ucfirst($field);
            if ($product->$getter() != $value) {
                $changes = true;
                break;
            }
        }

        if (!$changes) {
            return new JsonResponse(['message' => 'No se han detectado cambios']);
        }

        try {
            $entityManager->flush();
            return new JsonResponse([
                'message' => 'Cambios guardados correctamente',
                'needsRestock' => $product->needsRestock(),
                'hasEnoughStock' => $product->hasEnoughStock()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error al guardar los cambios: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/product/{id}', name: 'product_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($product);
                $entityManager->flush();
                $this->addFlash('success', 'Producto eliminado correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al eliminar el producto.');
            }
        }

        return $this->redirectToRoute('view_stock');
    }
}