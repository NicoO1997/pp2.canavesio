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
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('edit'.$product->getId(), $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF inválido'], 400);
        }

        $isGestorStock = $this->isGranted('ROLE_GESTORSTOCK');
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $isVendedor = $this->isGranted('ROLE_VENDEDOR');
        
        if (!$isGestorStock && !$isAdmin && !$isVendedor) {
            return new JsonResponse(['success' => false, 'message' => 'No tienes permisos suficientes'], 403);
        }

        try {
            if ($isGestorStock || $isAdmin) {
                $product->setQuantity((int)$request->request->get('quantity', $product->getQuantity()));
                $product->setMinStock((int)$request->request->get('minStock', $product->getMinStock()));
            }
            
            if ($isVendedor || $isAdmin) {
                $product->setName($request->request->get('name', $product->getName()));
                $product->setBrand($request->request->get('brand', $product->getBrand()));
                $product->setQuantity((int)$request->request->get('quantity', $product->getQuantity()));
                $product->setPrice((float)$request->request->get('price', $product->getPrice()));
                $product->setDescription($request->request->get('description', $product->getDescription()));
                
                if ($request->request->has('isEnabled')) {
                    $product->setIsEnabled($request->request->get('isEnabled') == '1');
                }
            }

            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Producto actualizado correctamente',
                'needsRestock' => $product->needsRestock(),
                'hasEnoughStock' => $product->hasEnoughStock()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false, 
                'message' => 'Error al actualizar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/product/{id}/toggle-status', name: 'product_toggle_status', methods: ['POST'])]
    public function toggleStatus(Request $request, Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isCsrfTokenValid('edit'.$product->getId(), $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF inválido'], 400);
        }
        
        if (!$this->isGranted('ROLE_VENDEDOR') && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['success' => false, 'message' => 'No tienes permisos suficientes'], 403);
        }

        try {
            $newStatus = $request->request->get('isEnabled') == '1';
            $product->setIsEnabled($newStatus);
            $entityManager->flush();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'newStatus' => $newStatus
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false, 
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/product/{id}/delete', name: 'product_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($product);
                $entityManager->flush();
                $this->addFlash('success', 'Producto eliminado correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al eliminar el producto: ' . $e->getMessage());
                error_log('Error en eliminación de producto: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF inválido: ' . $request->request->get('_token'));
        }
        return $this->redirect($this->generateUrl('view_stock'));
    }
}