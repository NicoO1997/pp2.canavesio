<?php
namespace App\Controller;

use App\Entity\RecipeMachine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeMachineController extends AbstractController
{
    #[Route('/create-recipe-machine', name: 'create_recipe_machine', methods: ['GET', 'POST'])]
    public function createRecipeMachine(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $recipeName = $request->request->get('recipe_name');
            $partsData = $request->request->all('parts');
            $productsData = $request->request->all('products');

            $recipe = new RecipeMachine();
            $recipe->setName($recipeName);
            $recipe->setParts($partsData);
            $recipe->setProducts($productsData);

            $em->persist($recipe);
            $em->flush();

            return new Response('Receta de Machine creada con éxito.');
        }

        return $this->render('create_recipe_machine.html.twig');
    }

    #[Route('/recipe-machine/{id}', name: 'show_recipe_machine', methods: ['GET'])]
    public function showRecipeMachine(int $id, EntityManagerInterface $em): Response
    {
        $recipe = $em->getRepository(RecipeMachine::class)->find($id);

        if (!$recipe) {
            throw $this->createNotFoundException('La receta no existe.');
        }

        return $this->render('show_recipe_machine.html.twig', [
            'recipe' => $recipe,
        ]);
    }



    #[Route('/edit-recipe-machine/{id}', name: 'edit_recipe_machine', methods: ['GET', 'POST'])]
    public function editRecipeMachine(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $recipe = $em->getRepository(RecipeMachine::class)->find($id);

        if (!$recipe) {
            throw $this->createNotFoundException('La receta no existe.');
        }

        if ($request->isMethod('POST')) {
            $recipeName = $request->request->get('recipe_name');
            $partsData = $request->request->get('parts', []);
            $productsData = $request->request->get('products', []);

            $recipe->setName($recipeName);
            $recipe->setParts($partsData);
            $recipe->setProducts($productsData);

            $em->flush();

            return new Response('Receta de Machine actualizada con éxito.');
        }

        return $this->render('edit_recipe_machine.html.twig', [
            'recipe' => $recipe,
        ]);
    }
}
