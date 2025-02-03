<?php
namespace App\Controller;

use App\Entity\RecipeProduct;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeProductController extends AbstractController
{
    #[Route('/create-recipe-product', name: 'create_recipe_product', methods: ['GET', 'POST'])]
    public function createRecipeProduct(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $recipeName = $request->request->get('recipe_name');
            $partsData = $request->request->all('parts');

            $recipe = new RecipeProduct();
            $recipe->setName($recipeName);
            $recipe->setParts($partsData);

            $em->persist($recipe);
            $em->flush();

            return new Response('Receta de Producto creada con éxito.');
        }

        return $this->render('create_recipe_product.html.twig');
    }

    #[Route('/recipe-product/{id}', name: 'show_recipe_product', methods: ['GET'])]
    public function showRecipeProduct(int $id, EntityManagerInterface $em): Response
    {
        $recipe = $em->getRepository(RecipeProduct::class)->find($id);

        if (!$recipe) {
            throw $this->createNotFoundException('La receta no existe.');
        }

        return $this->render('show_recipe_product.html.twig', [
            'recipe' => $recipe,
        ]);
    }
}