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
use Symfony\Component\HttpFoundation\File\UploadedFile;





class ProductController extends AbstractController
{
    #[Route('/product/new', name: 'new_product')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_VENDEDOR') && !$this->isGranted('ROLE_GESTORSTOCK')) {
            return $this->redirectToRoute('app_login');
        }

        $product = new Product();
        $product->setIsEnabled(false);
        $product->setCreatedBy('SantiAragon');
        $argentinaTime = new \DateTime('now', new \DateTimeZone('America/Argentina/Buenos_Aires'));
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
                    // Generar un nombre de archivo seguro
                    $originalFilename = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $imageFile->getClientOriginalName())));
                    $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                    // Mover el archivo
                    $imageFile->move(
                        'assets/imagenes',
                        $newFilename
                    );

                    // Guardar solo el nombre del archivo
                    $product->setImage($newFilename);

                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error al procesar la imagen: ' . $e->getMessage());
                    return $this->redirectToRoute('new_product');
                }
            }

            try {
                $entityManager->persist($product);
                $entityManager->flush();

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

        // Obtener los parámetros de filtro
        $category = $request->query->get('category');
        $partType = $request->query->get('partType');
        $brand = $request->query->get('brand');

        // Crear el QueryBuilder
        $queryBuilder = $entityManager->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->where('p.isEnabled = :isEnabled')
            ->setParameter('isEnabled', true);

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
        $products = $queryBuilder->getQuery()->getResult();

        // Obtener favoritos
        $favoriteProductIds = [];
        if ($user && !in_array('ROLE_INVITADO', $user->getRoles())) {
            $favorites = $userFavoriteProductRepository->findBy(['user' => $user]);
            $favoriteProductIds = array_map(fn($favorite) => $favorite->getProduct()->getId(), $favorites);
        }

        // Pasar sparePartsBrands a la vista
        return $this->render('product/list.html.twig', [
            'products' => $products,
            'favoriteProductIds' => $favoriteProductIds,
            'isGuest' => $user && in_array('ROLE_INVITADO', $user->getRoles()),
            'sparePartsBrands' => $this->getSparePartsBrands(), // Asegúrate de tener este método en tu controlador
        ]);
    }
    private function getSparePartsBrands(): array
    {
        return [
            // Repuestos para Tractores - Filtros
            'tractor_filtro_aire' => ['Mann+Hummel', 'Fleetguard', 'Donaldson', 'Baldwin', 'WIX', 'FRAM', 'Mahle', 'Luber-finer', 'Parker Filtration', 'K&N'],
            'tractor_filtro_aceite' => ['Mann+Hummel', 'Fleetguard', 'WIX', 'FRAM', 'Mahle', 'Baldwin', 'Donaldson', 'Luber-finer', 'Bosch', 'ACDelco'],
            'tractor_filtro_combustible' => ['Fleetguard', 'Mann+Hummel', 'Donaldson', 'Baldwin', 'Bosch', 'Delphi', 'Parker', 'WIX', 'FRAM', 'Mahle'],
            'tractor_filtro_hidraulico' => ['Hydac', 'Parker', 'Donaldson', 'Mann+Hummel', 'Fleetguard', 'Baldwin', 'Bosch Rexroth', 'MP Filtri', 'Mahle', 'Pall'],

            // Repuestos para Tractores - Correas
            'tractor_correa_transmision' => ['Gates', 'Continental', 'Dayco', 'Goodyear', 'Mitsuboshi', 'Optibelt', 'Bando', 'SKF', 'Jason', 'PIX'],
            'tractor_correa_alternador' => ['Gates', 'Dayco', 'Continental', 'Bosch', 'SKF', 'Mitsuboshi', 'Goodyear', 'Bando', 'Optibelt', 'PIX'],

            // Repuestos para Tractores - Sistema Eléctrico
            'tractor_bateria' => ['Bosch', 'Varta', 'Exide', 'Optima', 'ACDelco', 'Interstate', 'Deka', 'Yuasa', 'Banner', 'Odyssey'],
            'tractor_alternador' => ['Bosch', 'Denso', 'Delco Remy', 'Prestolite', 'Valeo', 'Mitsubishi Electric', 'Hitachi', 'Lucas', 'Nikko', 'Sawafuji'],
            'tractor_motor_arranque' => ['Bosch', 'Denso', 'Delco Remy', 'Valeo', 'Prestolite', 'Mitsubishi Electric', 'Hitachi', 'Lucas', 'Nikko', 'WAI'],
            'tractor_fusible' => ['Littelfuse', 'Bussmann', 'Cooper Bussmann', 'Ferraz Shawmut', 'Schneider Electric', 'ABB', 'Siemens', 'Phoenix Contact', 'TE Connectivity', 'Mersen'],

            // Repuestos para Tractores - Neumáticos y Llantas
            'tractor_neumatico' => ['Firestone', 'Michelin', 'Goodyear', 'BKT', 'Alliance', 'Continental', 'Trelleborg', 'Titan', 'Mitas', 'Bridgestone'],
            'tractor_llanta' => ['Titan', 'GKN Wheels', 'Accuride', 'Maxion Wheels', 'Pronar', 'Birrana', 'Stalker', 'Grasdorf', 'Starco', 'Wheel Works'],

            // Repuestos para Tractores - Motor
            'tractor_piston' => ['Mahle', 'Federal-Mogul', 'NPR', 'Nural', 'Sealed Power', 'DNJ Engine Components', 'IPD', 'United Engine & Machine', 'Wiseco', 'Ross Racing Pistons'],
            'tractor_bujia' => ['NGK', 'Bosch', 'Denso', 'Champion', 'ACDelco', 'Motorcraft', 'Autolite', 'BERU', 'Splitfire', 'E3'],
            'tractor_inyector' => ['Bosch', 'Delphi', 'Denso', 'Continental/VDO', 'Siemens/VDO', 'Stanadyne', 'Yanmar', 'Zexel', 'Lucas', 'Caterpillar'],
            'tractor_radiador' => ['Modine', 'Valeo', 'Denso', 'Nissens', 'Behr', 'CSF', 'TYC', 'APDI', 'Spectra Premium', 'Vista-Pro'],

            // Repuestos para Tractores - Sistema Hidráulico
            'tractor_bomba_hidraulica' => ['Parker', 'Bosch Rexroth', 'Eaton', 'Danfoss', 'Casappa', 'Bondioli & Pavesi', 'Sauer-Danfoss', 'Commercial', 'Prince', 'Cross'],
            'tractor_manguera_hidraulica' => ['Parker', 'Gates', 'Eaton', 'Bridgestone', 'Continental', 'Manuli', 'Alfagomma', 'Semperit', 'Dunlop', 'Goodyear'],
            'tractor_valvula_hidraulica' => ['Parker', 'Bosch Rexroth', 'Eaton', 'Danfoss', 'Sun Hydraulics', 'Hydac', 'Walvoil', 'Prince', 'Cross', 'Brand'],

            // Repuestos para Cosechadoras
            'cosechadora_cuchilla_trigo' => ['John Deere', 'Case IH', 'New Holland', 'Claas', 'MacDon', 'Massey Ferguson', 'Deutz-Fahr', 'Laverda', 'Gleaner', 'Fendt'],
            'cosechadora_cuchilla_maiz' => ['John Deere', 'Case IH', 'New Holland', 'Claas', 'Capello', 'Olimac', 'Geringhoff', 'Drago', 'MacDon', 'Fantini'],
            'cosechadora_cuchilla_arroz' => ['John Deere', 'Case IH', 'New Holland', 'Claas', 'MacDon', 'Massey Ferguson', 'Kubota', 'Yanmar', 'ISEKI', 'Mitsubishi'],
            'cosechadora_tamiz' => ['John Deere', 'Case IH', 'New Holland', 'Claas', 'Massey Ferguson', 'Deutz-Fahr', 'AGCO', 'Laverda', 'Gleaner', 'Challenger'],
            'cosechadora_zaranda' => ['John Deere', 'Case IH', 'New Holland', 'Claas', 'Massey Ferguson', 'Deutz-Fahr', 'AGCO', 'Laverda', 'Gleaner', 'Challenger'],
            'cosechadora_correa' => ['Gates', 'Optibelt', 'Continental', 'Dayco', 'Goodyear', 'Mitsuboshi', 'Bando', 'SKF', 'Jason', 'PIX'],
            'cosechadora_cadena' => ['Regina', 'Diamond Chain', 'Renold', 'Tsubaki', 'IWIS', 'RK', 'DID', 'KMC', 'Donghua', 'Timken'],
            'cosechadora_rotor' => ['John Deere', 'Case IH', 'New Holland', 'Claas', 'Massey Ferguson', 'Deutz-Fahr', 'AGCO', 'Laverda', 'Gleaner', 'Challenger'],
            'cosechadora_sacudidor' => ['John Deere', 'Case IH', 'New Holland', 'Claas', 'Massey Ferguson', 'Deutz-Fahr', 'AGCO', 'Laverda', 'Gleaner', 'Challenger'],

            // Repuestos para Equipos de Siembra
            'siembra_disco' => ['John Deere', 'Precision Planting', 'Kinze', 'Great Plains', 'Case IH', 'Monosem', 'Amazone', 'Lemken', 'Väderstad', 'Semeato'],
            'siembra_cuchilla' => ['Bellota Agrisolutions', 'Ingersoll', 'John Deere', 'Case IH', 'Kinze', 'Great Plains', 'Horsch', 'Amazone', 'Kuhn', 'Lemken'],
            'siembra_dosificador' => ['Precision Planting', 'John Deere', 'Case IH', 'Kinze', 'Great Plains', 'Monosem', 'Amazone', 'Horsch', 'Kuhn', 'MaterMacc'],
            'siembra_tubo' => ['John Deere', 'Case IH', 'Kinze', 'Great Plains', 'Monosem', 'Amazone', 'Lemken', 'Horsch', 'Kuhn', 'Semeato'],
            'siembra_boquilla' => ['TeeJet', 'Hypro', 'Arag', 'Lechler', 'Albuz', 'Hardi', 'John Deere', 'Case IH', 'Amazone', 'Kuhn'],
            'siembra_rodamiento' => ['SKF', 'Timken', 'NTN', 'NSK', 'FAG', 'INA', 'Koyo', 'NACHI', 'FYH', 'NKE'],
            'siembra_eje' => ['John Deere', 'Case IH', 'Kinze', 'Great Plains', 'SKF', 'Timken', 'NTN', 'NSK', 'FAG', 'INA'],

            // Repuestos para Equipos de Riego
            'riego_aspersor' => ['Nelson', 'Rain Bird', 'Hunter', 'Senninger', 'Komet', 'Netafim', 'Valley', 'Lindsay', 'Reinke', 'T-L'],
            'riego_boquilla' => ['Nelson', 'TeeJet', 'Senninger', 'Rain Bird', 'Hunter', 'Komet', 'Netafim', 'Hypro', 'Arag', 'Lechler'],
            'riego_manguera' => ['Netafim', 'John Deere Water', 'Rain Bird', 'Hunter', 'Toro', 'Irritec', 'NaanDanJain', 'Rivulis', 'Jain', 'Plastro'],
            'riego_tuberia' => ['Charlotte Pipe', 'JM Eagle', 'Netafim', 'Rain Bird', 'Hunter', 'Irritec', 'NaanDanJain', 'Rivulis', 'Jain', 'Georg Fischer'],
            'riego_bomba' => ['Grundfos', 'Xylem', 'KSB', 'Wilo', 'Franklin Electric', 'Berkeley', 'Cornell', 'Pentair', 'Goulds', 'Caprari'],
            'riego_filtro' => ['Amiad', 'Netafim', 'Arkal', 'Rain Bird', 'Hunter', 'Azud', 'STF', 'Hydro-Flow', 'Spin Klin', 'Sand Master'],

            // Repuestos para Equipos de Forraje
            'forraje_cuchilla' => ['John Deere', 'New Holland', 'Case IH', 'Claas', 'Krone', 'Kuhn', 'Pöttinger', 'Vicon', 'Deutz-Fahr', 'Fella'],
            'forraje_martillo' => ['John Deere', 'New Holland', 'Case IH', 'Claas', 'Krone', 'Kuhn', 'Maschio', 'Pöttinger', 'Vogel & Noot', 'Seppi'],
            'forraje_barra' => ['John Deere', 'New Holland', 'Case IH', 'Claas', 'Krone', 'Kuhn', 'Pöttinger', 'Vicon', 'Deutz-Fahr', 'Fella'],
            'forraje_pua_segadora' => ['John Deere', 'New Holland', 'Case IH', 'Claas', 'Krone', 'Kuhn', 'Pöttinger', 'Vicon', 'Deutz-Fahr', 'Fella'],
            'forraje_pua_empacadora' => ['John Deere', 'New Holland', 'Case IH', 'Claas', 'Krone', 'Kuhn', 'Massey Ferguson', 'Deutz-Fahr', 'Welger', 'Vicon'],
            'forraje_cadena' => ['Regina', 'Diamond Chain', 'Renold', 'Tsubaki', 'IWIS', 'RK', 'DID', 'KMC', 'Donghua', 'Timken'],
            'forraje_engranaje' => ['Martin Sprocket', 'Boston Gear', 'QTC Metric Gears', 'Browning', 'Hub City', 'Rexnord', 'Dodge', 'Baldor', 'SEW-Eurodrive', 'Nord']
        ];
    }

    #[Route('/api/search-products', name: 'search_products', methods: ['GET'])]
    public function searchProducts(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $term = $request->query->get('term', '');

        if (empty($term)) {
            return new JsonResponse([]);
        }

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('p')
            ->from(Product::class, 'p')
            ->where('LOWER(p.name) LIKE LOWER(:term)')
            ->orWhere('LOWER(p.description) LIKE LOWER(:term)')
            ->orWhere('LOWER(p.brand) LIKE LOWER(:term)')
            ->orWhere('LOWER(p.partNumber) LIKE LOWER(:term)')
            ->setParameter('term', '%' . $term . '%')
            ->setMaxResults(10)
            ->orderBy('p.name', 'ASC');

        $products = $queryBuilder->getQuery()->getResult();

        $results = array_map(function ($product) {
            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'brand' => $product->getBrand(),
                'partNumber' => $product->getPartNumber()
            ];
        }, $products);

        return new JsonResponse($results);
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
            $product->setQuantity((int) $request->request->get('quantity'));
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
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', 'Producto eliminado correctamente');
        } else {
            $this->addFlash('error', 'Token CSRF inválido');
        }

        return $this->redirectToRoute('view_stock');
    }
}