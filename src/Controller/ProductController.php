<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\ProductMovement;
use App\Entity\UserFavoriteProduct;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\UserFavoriteProductRepository;
use App\Entity\Favorite;
use App\Service\ProductMovementService;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use DateTime;
use DateTimeZone;

class ProductController extends AbstractController
{
    private $productMovementService;

    public function __construct(ProductMovementService $productMovementService)
    {
        $this->productMovementService = $productMovementService;
    }

    #[Route('/product/new', name: 'new_product')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isGranted('ROLE_VENDEDOR') && !$this->isGranted('ROLE_GESTORSTOCK')) {
            return $this->redirectToRoute('app_login');
        }

        $product = new Product();
        $product->setIsEnabled(false);
        $product->setCreatedBy('NicoO1997');
        $product->setQuantity(0); // Establecer cantidad inicial en 0
        $argentinaTime = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
        $product->setCreatedAt($argentinaTime);

        if (!$this->isGranted('ROLE_GESTORSTOCK')) {
            $product->setMinStock(0);
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                try {
                    $originalFilename = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $imageFile->getClientOriginalName())));
                    $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                    // Use the configured images_directory instead of hardcoded path
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );

                    $product->setImage($newFilename);

                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error al procesar la imagen: ' . $e->getMessage());
                    return $this->redirectToRoute('new_product');
                }
            }

            try {
                // Obtener la cantidad del formulario
                $initialQuantity = $form->get('quantity')->getData();

                // Establecer la cantidad inicial en 0
                $product->setQuantity(0);

                // Persistir el producto con cantidad 0
                $entityManager->persist($product);
                $entityManager->flush();

                // Si hay una cantidad inicial, registrar como entrada
                if ($initialQuantity > 0) {
                    $this->productMovementService->recordEntry(
                        $product,
                        $initialQuantity,
                        'Stock inicial del producto'
                    );

                    // Actualizar la cantidad del producto
                    $product->setQuantity($initialQuantity);
                    $entityManager->flush();
                }

                $this->addFlash('success', 'Producto creado exitosamente');
                return $this->redirectToRoute('view_stock');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al guardar el producto: ' . $e->getMessage());
                return $this->redirectToRoute('new_product');
            }
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
            'sparePartsBrands' => $this->getSparePartsBrands()
        ]);
    }



    #[Route('/stock', name: 'view_stock')]
    public function viewStock(
        EntityManagerInterface $entityManager,
        UserFavoriteProductRepository $userFavoriteProductRepository
    ): Response {
        if (!$this->isGranted('ROLE_VENDEDOR') && !$this->isGranted('ROLE_GESTORSTOCK')) {
            return $this->redirectToRoute('app_login');
        }

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
        Request $request,
        EntityManagerInterface $entityManager,
        UserFavoriteProductRepository $userFavoriteProductRepository
    ): Response {
        $user = $this->getUser();

        $categories = $entityManager->getRepository(Category::class)
            ->findAll();

        // Obtener los parámetros de filtro
        $category = $request->query->get('category');
        $partType = $request->query->get('partType');
        $brand = $request->query->get('brand');
        $searchTerm = $request->query->get('search'); // Agregar parámetro de búsqueda

        // Obtener el repositorio
        $repository = $entityManager->getRepository(Product::class);

        // Crear el QueryBuilder basado en findAllActive
        $queryBuilder = $repository->createQueryBuilder('p')
            ->where('p.isDeleted = :isDeleted')
            ->andWhere('p.isEnabled = :isEnabled')
            ->setParameter('isDeleted', false)
            ->setParameter('isEnabled', true);

        // Aplicar búsqueda si existe
        if ($searchTerm) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    'LOWER(p.name) LIKE LOWER(:searchTerm)',
                    'LOWER(p.description) LIKE LOWER(:searchTerm)',
                    'LOWER(p.partNumber) LIKE LOWER(:searchTerm)'
                )
            )
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        // Aplicar filtros si están presentes
        if ($partType) {
            $queryBuilder->andWhere('p.partType = :partType')
                ->setParameter('partType', $partType);
        }

        if ($brand) {
            $queryBuilder->andWhere('p.brand = :brand')
                ->setParameter('brand', $brand);
        }

        // Ejecutar la consulta
        $products = $queryBuilder
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();

        // Obtener favoritos
        $favoriteProductIds = [];
        if ($user && !in_array('ROLE_INVITADO', $user->getRoles())) {
            $favorites = $userFavoriteProductRepository->findBy(['user' => $user]);
            $favoriteProductIds = array_map(fn($favorite) => $favorite->getProduct()->getId(), $favorites);
        }

        // Pasar todos los parámetros necesarios a la vista
        return $this->render('product/list.html.twig', [
            'products' => $products,
            'favoriteProductIds' => $favoriteProductIds,
            'isGuest' => $user && in_array('ROLE_INVITADO', $user->getRoles()),
            'categories' => $categories,
            'sparePartsBrands' => $this->getSparePartsBrands(),
            'searchTerm' => $searchTerm, // Pasar el término de búsqueda a la vista
            'selectedBrand' => $brand,   // Pasar la marca seleccionada a la vista
            'selectedPartType' => $partType // Pasar el tipo de parte seleccionado a la vista
        ]);
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

    #[Route('/api/search-products', name: 'search_products', methods: ['GET'])]
    public function searchProducts(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $searchTerm = $request->query->get('search');
        $brand = $request->query->get('brand');

        if (empty($searchTerm)) {
            return new JsonResponse([
                'success' => true,
                'results' => []
            ]);
        }

        // Crear el QueryBuilder
        $queryBuilder = $entityManager->getRepository(Product::class)->createQueryBuilder('p');

        // Construir la consulta
        $queryBuilder
            ->where('p.isDeleted = :isDeleted')
            ->andWhere('p.isEnabled = :isEnabled')
            ->andWhere(
                $queryBuilder->expr()->orX(
                    'LOWER(p.name) LIKE LOWER(:searchTerm)',
                    'LOWER(p.description) LIKE LOWER(:searchTerm)',
                    'LOWER(p.partNumber) LIKE LOWER(:searchTerm)'
                )
            )
            ->setParameter('isDeleted', false)
            ->setParameter('isEnabled', true)
            ->setParameter('searchTerm', '%' . $searchTerm . '%');

        // Aplicar filtro de marca si existe
        if ($brand) {
            $queryBuilder->andWhere('p.brand = :brand')
                ->setParameter('brand', $brand);
        }

        // Ejecutar la consulta
        $products = $queryBuilder
            ->orderBy('p.name', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Mapear los resultados
        $results = array_map(function ($product) {
            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'brand' => $product->getBrand(),
                'partNumber' => $product->getPartNumber(),
                'image' => $product->getImage()
            ];
        }, $products);

        return new JsonResponse([
            'success' => true,
            'results' => $results
        ]);
    }

    /**
     * Calcula la relevancia del resultado basado en dónde se encontró el término
     * @param Product $product El producto a evaluar
     * @param string $searchTerm El término de búsqueda normalizado
     * @return int Puntuación de relevancia
     */
    private function getMatchType(Product $product, string $searchTerm): int
    {
        $matches = 0;

        // Normalizar los valores del producto para la comparación
        $partNumber = strtolower($product->getPartNumber() ?? '');
        $name = strtolower($product->getName() ?? '');
        $brand = strtolower($product->getBrand() ?? '');
        $description = strtolower($product->getDescription() ?? '');

        // Coincidencia exacta en número de parte (prioridad más alta)
        if (!empty($partNumber) && stripos($partNumber, $searchTerm) !== false) {
            $matches += 4;
        }

        // Coincidencia en nombre
        if (!empty($name) && stripos($name, $searchTerm) !== false) {
            $matches += 3;
        }

        // Coincidencia en marca
        if (!empty($brand) && stripos($brand, $searchTerm) !== false) {
            $matches += 2;
        }

        // Coincidencia en descripción
        if (!empty($description) && stripos($description, $searchTerm) !== false) {
            $matches += 1;
        }

        return $matches;
    }

    #[Route('/product/{id}', name: 'product_detail')]
    public function detail(
        int $id,
        EntityManagerInterface $entityManager,
        UserFavoriteProductRepository $userFavoriteProductRepository
    ): Response {
        $user = $this->getUser();
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('El producto no existe');
        }

        // Determinar el estado del stock y su información
        $stockInfo = $this->getStockInfo($product->getQuantity());

        $favoriteProductIds = [];
        if ($user && !in_array('ROLE_INVITADO', $user->getRoles())) {
            $favorites = $userFavoriteProductRepository->findBy(['user' => $user]);
            $favoriteProductIds = array_map(function ($favorite) {
                return $favorite->getProduct()->getId();
            }, $favorites);
        }

        return $this->render('product/detail.html.twig', [
            'product' => $product,
            'favoriteProductIds' => $favoriteProductIds,
            'isGuest' => $user && in_array('ROLE_INVITADO', $user->getRoles()),
            'stockInfo' => $stockInfo
        ]);
    }

    private function getStockInfo(int $quantity): array
    {
        if ($quantity <= 0) {
            return [
                'status' => 'Sin Stock',
                'class' => 'stock-empty',
                'color' => '#dc3545',
                'icon' => 'fa-times-circle',
                'message' => 'Producto temporalmente sin stock'
            ];
        } elseif ($quantity <= 5) {
            return [
                'status' => '¡Últimas unidades!',
                'class' => 'stock-low',
                'color' => '#ffc107',
                'icon' => 'fa-exclamation-circle',
                'message' => 'Quedan pocas unidades disponibles'
            ];
        } else {
            return [
                'status' => 'En Stock',
                'class' => 'stock-available',
                'color' => '#28a745',
                'icon' => 'fa-check-circle',
                'message' => 'Producto disponible'
            ];
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

        if (!$user || in_array('ROLE_INVITADO', $user->getRoles())) {
            $this->addFlash('info', 'Necesitas registrarte para agregar productos a favoritos');
            return $this->redirectToRoute('app_register');
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

    #[Route('/product/{id}/delete', name: 'product_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Product $product,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isGranted('ROLE_VENDEDOR') && !$this->isGranted('ROLE_GESTORSTOCK')) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            // Registrar el movimiento de eliminación antes de eliminar el producto
            $this->productMovementService->recordDeletion(
                $product,
                'Eliminación del producto'
            );

            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', 'Producto eliminado correctamente');
        } else {
            $this->addFlash('error', 'Token CSRF inválido');
        }

        return $this->redirectToRoute('view_stock');
    }

    #[Route('/product/edit/{id}', name: 'product_edit', methods: ['POST'])]
    public function editProduct(
        Request $request,
        EntityManagerInterface $entityManager,
        Product $product
    ): JsonResponse {
        if (!$this->isGranted('ROLE_VENDEDOR') && !$this->isGranted('ROLE_GESTORSTOCK')) {
            return new JsonResponse(['message' => 'Acceso denegado'], 403);
        }

        $csrfToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('edit' . $product->getId(), $csrfToken)) {
            return new JsonResponse(['message' => 'Token CSRF inválido.'], 400);
        }

        // Guardar la cantidad anterior antes de actualizarla
        $oldQuantity = $product->getQuantity();

        if ($request->request->has('isEnabled')) {
            $isEnabled = $request->request->get('isEnabled') === '1';
            $product->setIsEnabled($isEnabled);
        }

        if ($request->request->has('name')) {
            $product->setName($request->request->get('name'));
        }

        if ($request->request->has('brand')) {
            $product->setBrand($request->request->get('brand'));
        }

        if ($request->request->has('quantity')) {
            $newQuantity = (int) $request->request->get('quantity');
            $difference = $newQuantity - $oldQuantity;

            if ($difference !== 0) {
                if ($difference > 0) {
                    $this->productMovementService->recordEntry(
                        $product,
                        abs($difference),
                        'Incremento manual de stock'
                    );
                } else {
                    $this->productMovementService->recordSale(
                        $product,
                        abs($difference),
                        'Decremento manual de stock'
                    );
                }
            }

            $product->setQuantity($newQuantity);
        }

        if ($request->request->has('minStock')) {
            $product->setMinStock((int) $request->request->get('minStock'));
        }

        if ($request->request->has('price')) {
            $product->setPrice((float) $request->request->get('price'));
        }

        if ($request->request->has('description')) {
            $product->setDescription($request->request->get('description'));
        }

        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Producto actualizado correctamente.'
        ]);
    }
}