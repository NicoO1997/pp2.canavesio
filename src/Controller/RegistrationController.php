<?php

namespace App\Controller;
 
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormError;

class RegistrationController extends AbstractController
{
    #[Route('/test', name:'test')]
    public function main(): Response
    {
        return $this->render('base.html.twig');
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $plainPassword = $form->get('plainPassword')->getData();
            
            // Validación estricta de la longitud
            if (strlen($plainPassword) > 12) {
                $form->get('plainPassword')->addError(new FormError('La contraseña no puede superar los 255 caracteres.'));
            }

            if ($form->isValid()) {
                try {
                    $user->setSecurityQuestion($form->get('securityQuestion')->getData());
                    $user->setSecurityAnswer($form->get('securityAnswer')->getData());
                    
                    // Obtener el email del usuario
                    $email = $user->getEmail();
                    
                    // Lógica de asignación de roles
                    $roles = ['ROLE_USER'];
                    if (strpos($email, 'admin@prueba.com') !== false) {
                        $roles = ['ROLE_ADMIN'];
                    } elseif (strpos($email, 'stock@prueba.com') !== false) {
                        $roles = ['ROLE_GESTORSTOCK'];              
                    } elseif (strpos($email, 'ventas@prueba.com') !== false) {
                        $roles = ['ROLE_VENDEDOR'];
                    }
            
                    // Asignar los roles al usuario
                    $user->setRoles($roles);
            
                    // Codificar contraseña
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $plainPassword
                        )
                    );
            
                    $entityManager->persist($user);
                    $entityManager->flush();
            
                    return $this->redirectToRoute('app_login');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Ha ocurrido un error al registrar el usuario.');
                    return $this->redirectToRoute('app_register');
                }
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}