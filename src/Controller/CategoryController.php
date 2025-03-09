<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


class CategoryController extends AbstractController
{
    private $entityManager;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    #[Route('/category', name: 'app_category_list', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->entityManager
            ->getRepository(Category::class)
            ->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/category/new', name: 'app_category_new', methods: ['GET', 'POST'])]
public function new(Request $request): Response
{
    $this->denyAccessUnlessGranted('ROLE_VENDEDOR');

    $category = new Category();
    $form = $this->createForm(CategoryType::class, $category);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        try {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Sanitizar el nombre del archivo
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'), // Usar images_directory en lugar de categories_directory
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error al subir la imagen: ' . $e->getMessage());
                    return $this->render('category/new.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

                $category->setImage($newFilename);
            }

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'Categoría creada exitosamente.');
            return $this->redirectToRoute('app_category_list');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al crear la categoría: ' . $e->getMessage());
        }
    }

    return $this->render('category/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

    #[Route('/category/update/{id}', name: 'app_category_update', methods: ['POST'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_VENDEDOR');

        $category = $this->entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            return $this->json(['success' => false, 'message' => 'Categoría no encontrada'], 404);
        }

        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return $this->json(['success' => false, 'message' => 'Datos inválidos'], 400);
            }

            $constraints = new Assert\Collection([
                'name' => [
                    new Assert\NotBlank(['message' => 'El nombre no puede estar vacío']),
                    new Assert\Length(['min' => 1, 'max' => 255])
                ],
                'code' => [
                    new Assert\NotBlank(['message' => 'El código no puede estar vacío']),
                    new Assert\Length(['min' => 1, 'max' => 50])
                ]
            ]);

            $violations = $this->validator->validate($data, $constraints);

            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $propertyPath = $violation->getPropertyPath();
                    $field = str_replace(['[', ']'], '', $propertyPath);
                    $errors[$field] = $violation->getMessage();
                }
                return $this->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $errors
                ], 400);
            }

            $name = trim($data['name']);
            $code = trim($data['code']);

            if (empty($name) || empty($code)) {
                $errors = [];
                if (empty($name)) {
                    $errors['name'] = 'El nombre no puede estar vacío';
                }
                if (empty($code)) {
                    $errors['code'] = 'El código no puede estar vacío';
                }

                return $this->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $errors
                ], 400);
            }

            $category->setName($name);
            $category->setCode($code);

            $this->entityManager->flush();

            return $this->json(['success' => true, 'message' => 'Categoría actualizada exitosamente']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/category/delete/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_VENDEDOR');

        $category = $this->entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            return $this->json(['success' => false, 'message' => 'Categoría no encontrada'], 404);
        }

        try {
            $this->entityManager->remove($category);
            $this->entityManager->flush();

            return $this->json(['success' => true, 'message' => 'Categoría eliminada exitosamente']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}