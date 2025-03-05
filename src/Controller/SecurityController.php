<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SecurityController extends AbstractController
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    #[Route(path: '/login-guest', name: 'app_login_guest')]
public function loginAsGuest(
    Request $request,
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher
): Response {
    if ($this->getUser()) {
        return $this->redirectToRoute('app_home_page');
    }

    try {
        $guestUser = new User();
        $uniqueId = uniqid('guest_', true);
        $guestEmail = 'guest_' . $uniqueId . '@temp.com';
        $guestUsername = 'Guest_' . $uniqueId;
        $tempPassword = bin2hex(random_bytes(8));

        $guestUser->setEmail($guestEmail);
        $guestUser->setUsername($guestUsername);
        $guestUser->setIsGuest(true);
        $guestUser->setRoles(['ROLE_INVITADO']);
        $guestUser->setPassword($passwordHasher->hashPassword($guestUser, $tempPassword));
        $guestUser->setSecurityQuestion('Guest Question');
        $guestUser->setSecurityAnswer('Guest Answer');

        $entityManager->persist($guestUser);
        $entityManager->flush();

        $token = new UsernamePasswordToken(
            $guestUser,
            'main',
            ['ROLE_INVITADO']
        );

        $this->tokenStorage->setToken($token);
        
        $request->getSession()->set('_security_main', serialize($token));

        $event = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher->dispatch($event);

        return $this->redirectToRoute('app_home_page');

    } catch (\Exception $e) {
        $this->addFlash('error', 'Error al iniciar sesión como invitado');
        return $this->redirectToRoute('app_login');
    }
}

#[Route(path: '/login', name: 'app_login')]
public function login(AuthenticationUtils $authenticationUtils): Response
{
    $this->getUser();
    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();

    return $this->render('security/login.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error,
    ]);
}

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}