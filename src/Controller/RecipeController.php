<?php
namespace App\Controller;

use App\Entity\RecipeMachine;
use App\Entity\RecipeProduct;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeController extends AbstractController
{
    #[Route('/recipes', name: 'show_all_recipes', methods: ['GET'])]
    public function showAllRecipes(EntityManagerInterface $em): Response
    {
        $machineRecipes = $em->getRepository(RecipeMachine::class)->findAll();
        $productRecipes = $em->getRepository(RecipeProduct::class)->findAll();

        return $this->render('show_all_recipes.html.twig', [
            'machine_recipes' => $machineRecipes,
            'product_recipes' => $productRecipes,
        ]);
    }
}
