<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductMovement;
use App\Entity\CartProductOrder;
use App\Repository\ProductRepository;
use App\Service\ProductMovementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends AbstractController
{
    private $productMovementService;

    public function __construct(ProductMovementService $productMovementService)
    {
        $this->productMovementService = $productMovementService;
    }

    #[Route('/stock/view', name: 'view_stock')]
    public function viewStock(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findAllActive();
        $sparePartsBrands = $this->getSparePartsBrands();

        return $this->render('stock/view_stock.html.twig', [
            'products' => $products,
            'sparePartsBrands' => $sparePartsBrands
        ]);
    }

    #[Route('/product/{id}/edit', name: 'product_edit', methods: ['POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('edit' . $product->getId(), $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF inválido'], 400);
        }
    
        $isGestorStock = $this->isGranted('ROLE_GESTORSTOCK');
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $isVendedor = $this->isGranted('ROLE_VENDEDOR');
    
        if (!$isGestorStock && !$isAdmin && !$isVendedor) {
            return new JsonResponse(['success' => false, 'message' => 'No tienes permisos suficientes'], 403);
        }
    
        try {
            // Guardar el stock original ANTES de cualquier cambio
            $originalQuantity = $product->getQuantity();
    
            if ($isGestorStock) {
                // Procesar solo quantity y minStock para ROLE_GESTORSTOCK
                if ($request->request->has('quantity')) {
                    $newQuantity = (int) $request->request->get('quantity');
                    if ($newQuantity !== $originalQuantity) {
                        // Registrar el movimiento antes de actualizar la cantidad
                        $this->productMovementService->recordMovement(
                            $product,
                            ProductMovement::TYPE_EDIT,
                            $newQuantity - $originalQuantity,
                            $originalQuantity,
                            $newQuantity,
                            sprintf('Modificación manual de stock de %d a %d por Gestor Stock', $originalQuantity, $newQuantity)
                        );
                    }
                    $product->setQuantity($newQuantity);
                }
                if ($request->request->has('minStock')) {
                    $product->setMinStock((int) $request->request->get('minStock'));
                }
            } elseif ($isVendedor || $isAdmin) {
                // Procesar todos los campos para ROLE_VENDEDOR y ROLE_ADMIN
                foreach ($request->request->all() as $field => $value) {
                    if ($field !== '_token' && $field !== 'isEnabled' && !str_ends_with($field, '_text')) {
                        $setter = 'set' . ucfirst($field);
                        if (method_exists($product, $setter)) {
                            if (in_array($field, ['quantity', 'minStock', 'estimatedLifeHours'])) {
                                $value = (int) $value;
                                if ($field === 'quantity' && $value !== $originalQuantity) {
                                    $this->productMovementService->recordMovement(
                                        $product,
                                        ProductMovement::TYPE_EDIT,
                                        $value - $originalQuantity,
                                        $originalQuantity,
                                        $value,
                                        sprintf('Modificación manual de stock de %d a %d por Vendedor', $originalQuantity, $value)
                                    );
                                }
                                $product->$setter($value);
                            } elseif ($field === 'price' || $field === 'weight') {
                                $product->$setter((float) $value);
                            } else {
                                $product->$setter($value);
                            }
                        }
                    }
                }
            }
    
            if (($isVendedor || $isAdmin) && $request->request->has('isEnabled')) {
                $product->setIsEnabled($request->request->get('isEnabled') == '1');
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
        if (!$this->isCsrfTokenValid('edit' . $product->getId(), $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF inválido'], 400);
        }

        if (!$this->isGranted('ROLE_VENDEDOR') && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['success' => false, 'message' => 'No tienes permisos suficientes'], 403);
        }

        try {
            $newStatus = $request->request->get('isEnabled') == '1';
            $product->setIsEnabled($newStatus);

            // Registrar el cambio de estado como un movimiento de ajuste
            $this->productMovementService->recordAdjustment(
                $product,
                $product->getQuantity(),
                'Cambio de estado a ' . ($newStatus ? 'habilitado' : 'deshabilitado')
            );

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
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            try {
                // Registrar el movimiento antes de la eliminación suave
                $this->productMovementService->recordDeletion(
                    $product,
                    sprintf(
                        'Eliminación lógica del producto %s realizada por %s',
                        $product->getName(),
                        'SantiAragon'
                    )
                );

                // Realizar la eliminación suave
                $product->softDelete();
                $entityManager->flush();

                $this->addFlash('success', 'Producto eliminado correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al eliminar el producto: ' . $e->getMessage());
                error_log('Error en eliminación de producto: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF inválido');
        }

        return $this->redirect($this->generateUrl('view_stock'));
    }


    #[Route('/stock/deleted', name: 'view_deleted_stock')]
    public function viewDeletedStock(ProductRepository $productRepository): Response
    {
        $deletedProducts = $productRepository->findAllDeleted();

        return $this->render('stock/view_deleted_stock.html.twig', [
            'products' => $deletedProducts
        ]);
    }

    #[Route('/product/{id}/permanent-delete', name: 'product_permanent_delete', methods: ['POST'])]
    public function permanentDelete(
        Request $request,
        Product $product,
        EntityManagerInterface $entityManager
    ): Response {
        // Verificar el token CSRF
        if (!$this->isCsrfTokenValid('permanent_delete' . $product->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF inválido');
            return $this->redirectToRoute('view_deleted_stock');
        }

        // Verificar permisos
        if (
            !$this->isGranted('ROLE_ADMIN') &&
            !$this->isGranted('ROLE_VENDEDOR') &&
            !$this->isGranted('ROLE_GESTORSTOCK')
        ) {
            $this->addFlash('error', 'No tienes permisos para eliminar permanentemente productos');
            return $this->redirectToRoute('view_deleted_stock');
        }

        try {
            $entityManager->beginTransaction();

            try {
                // 1. Registrar el movimiento de eliminación permanente
                $this->productMovementService->recordPermanentDeletion(
                    $product,
                    sprintf(
                        'Eliminación permanente del producto %s realizada por %s (%s) - %s',
                        $product->getName(),
                        'SantiAragon',
                        $this->getUserRole($this->getUser()),
                        (new \DateTime('2025-03-09 17:27:54'))->format('Y-m-d H:i:s')
                    )
                );

                // 2. Eliminar las referencias en cart_product_order
                foreach ($product->getCartProductOrder() as $cartOrder) {
                    $entityManager->remove($cartOrder);
                }

                // 3. Eliminar el producto (los movimientos quedarán con product_id = NULL)
                $entityManager->remove($product);
                $entityManager->flush();
                $entityManager->commit();

                $this->addFlash('success', 'Producto eliminado permanentemente con éxito.');
            } catch (\Exception $e) {
                $entityManager->rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al eliminar permanentemente el producto: ' . $e->getMessage());
            error_log('Error en eliminación permanente de producto: ' . $e->getMessage());
        }

        return $this->redirectToRoute('view_deleted_stock');
    }

    private function getUserRole($user): string
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return 'Administrador';
        }
        if ($this->isGranted('ROLE_GESTORSTOCK')) {
            return 'Gestor de Stock';
        }
        if ($this->isGranted('ROLE_VENDEDOR')) {
            return 'Vendedor';
        }
        return 'Usuario';
    }

    #[Route('/product/{id}/restore', name: 'product_restore', methods: ['POST'])]
    public function restore(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('restore' . $product->getId(), $request->request->get('_token'))) {
            try {
                $product->restore();

                // Registrar la restauración como un movimiento de entrada
                $this->productMovementService->recordEntry(
                    $product,
                    $product->getQuantity(),
                    'Restauración del producto'
                );

                $entityManager->flush();

                $this->addFlash('success', 'Producto restaurado correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al restaurar el producto: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF inválido');
        }

        return $this->redirect($this->generateUrl('view_deleted_stock'));
    }

    private function getSparePartsBrands(): array
    {
        $allBrands = [
            'Mann+Hummel',
            'Fleetguard',
            'Donaldson',
            'Baldwin',
            'WIX',
            'FRAM',
            'Mahle',
            'Luber-finer',
            'Parker Filtration',
            'K&N',
            'Bosch',
            'ACDelco',
            'Delphi',
            'Parker',
            'Hydac',
            'Bosch Rexroth',
            'MP Filtri',
            'Pall',
            'Gates',
            'Continental',
            'Dayco',
            'Goodyear',
            'Mitsuboshi',
            'Optibelt',
            'Bando',
            'SKF',
            'Jason',
            'PIX',
            'Varta',
            'Exide',
            'Optima',
            'Interstate',
            'Deka',
            'Yuasa',
            'Banner',
            'Odyssey',
            'Denso',
            'Delco Remy',
            'Prestolite',
            'Valeo',
            'Mitsubishi Electric',
            'Hitachi',
            'Lucas',
            'Nikko',
            'Sawafuji',
            'WAI',
            'Littelfuse',
            'Bussmann',
            'Cooper Bussmann',
            'Ferraz Shawmut',
            'Schneider Electric',
            'ABB',
            'Siemens',
            'Phoenix Contact',
            'TE Connectivity',
            'Mersen',
            'Firestone',
            'Michelin',
            'BKT',
            'Alliance',
            'Trelleborg',
            'Titan',
            'Mitas',
            'Bridgestone',
            'GKN Wheels',
            'Accuride',
            'Maxion Wheels',
            'Pronar',
            'Birrana',
            'Stalker',
            'Grasdorf',
            'Starco',
            'Wheel Works',
            'Federal-Mogul',
            'NPR',
            'Nural',
            'Sealed Power',
            'DNJ Engine Components',
            'IPD',
            'United Engine & Machine',
            'Wiseco',
            'Ross Racing Pistons',
            'NGK',
            'Champion',
            'Motorcraft',
            'Autolite',
            'BERU',
            'Splitfire',
            'E3',
            'Continental/VDO',
            'Siemens/VDO',
            'Stanadyne',
            'Yanmar',
            'Zexel',
            'Caterpillar',
            'Modine',
            'Nissens',
            'Behr',
            'CSF',
            'TYC',
            'APDI',
            'Spectra Premium',
            'Vista-Pro',
            'Eaton',
            'Danfoss',
            'Casappa',
            'Bondioli & Pavesi',
            'Sauer-Danfoss',
            'Commercial',
            'Prince',
            'Cross',
            'Manuli',
            'Alfagomma',
            'Semperit',
            'Dunlop',
            'Sun Hydraulics',
            'Hydac',
            'Walvoil',
            'Brand',
            'John Deere',
            'Case IH',
            'New Holland',
            'Claas',
            'MacDon',
            'Massey Ferguson',
            'Deutz-Fahr',
            'Laverda',
            'Gleaner',
            'Fendt',
            'Capello',
            'Olimac',
            'Geringhoff',
            'Drago',
            'Fantini',
            'Kubota',
            'Yanmar',
            'ISEKI',
            'Mitsubishi',
            'AGCO',
            'Challenger',
            'Regina',
            'Diamond Chain',
            'Renold',
            'Tsubaki',
            'IWIS',
            'RK',
            'DID',
            'KMC',
            'Donghua',
            'Timken',
            'Precision Planting',
            'Kinze',
            'Great Plains',
            'Monosem',
            'Amazone',
            'Lemken',
            'Väderstad',
            'Semeato',
            'Bellota Agrisolutions',
            'Ingersoll',
            'Horsch',
            'Kuhn',
            'MaterMacc',
            'TeeJet',
            'Hypro',
            'Arag',
            'Lechler',
            'Albuz',
            'Hardi',
            'NTN',
            'NSK',
            'FAG',
            'INA',
            'Koyo',
            'NACHI',
            'FYH',
            'NKE',
            'Nelson',
            'Rain Bird',
            'Hunter',
            'Senninger',
            'Komet',
            'Netafim',
            'Valley',
            'Lindsay',
            'Reinke',
            'T-L',
            'John Deere Water',
            'Toro',
            'Irritec',
            'NaanDanJain',
            'Rivulis',
            'Jain',
            'Plastro',
            'Charlotte Pipe',
            'JM Eagle',
            'Georg Fischer',
            'Grundfos',
            'Xylem',
            'KSB',
            'Wilo',
            'Franklin Electric',
            'Berkeley',
            'Cornell',
            'Pentair',
            'Goulds',
            'Caprari',
            'Amiad',
            'Arkal',
            'Azud',
            'STF',
            'Hydro-Flow',
            'Spin Klin',
            'Sand Master',
            'Krone',
            'Pöttinger',
            'Vicon',
            'Fella',
            'Maschio',
            'Vogel & Noot',
            'Seppi',
            'Welger',
            'Martin Sprocket',
            'Boston Gear',
            'QTC Metric Gears',
            'Browning',
            'Hub City',
            'Rexnord',
            'Dodge',
            'Baldor',
            'SEW-Eurodrive',
            'Nord'
        ];

        // Eliminar duplicados y ordenar alfabéticamente
        $uniqueBrands = array_unique($allBrands);
        sort($uniqueBrands);

        return $uniqueBrands;
    }
}