<?php
 
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ForgotPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password')]
public function forgotPassword(Request $request, EntityManagerInterface $em): Response
{
    if ($request->isMethod('POST')) {
        $usernameOrEmail = $request->request->get('username_or_email');
        $securityQuestion = $request->request->get('securityQuestion');
        $securityAnswer = $request->request->get('securityAnswer');

        // Buscar por correo o nombre de usuario
        $user = $em->getRepository(User::class)->findOneBy(['email' => $usernameOrEmail]) ??
                $em->getRepository(User::class)->findOneBy(['username' => $usernameOrEmail]);

        if (!$user) {
            $this->addFlash('error', 'No se encontró ningún usuario con ese correo o nombre de usuario.');
            return $this->redirectToRoute('app_forgot_password');
        }

        // Validar pregunta y respuesta de seguridad
        if ($user->getSecurityQuestion() !== $securityQuestion || $user->getSecurityAnswer() !== $securityAnswer) {
            $this->addFlash('error', 'La pregunta o la respuesta de seguridad no coinciden.');
            return $this->redirectToRoute('app_forgot_password');
        }

        // Generar un token temporal
        $token = bin2hex(random_bytes(8)); // Token de 16 caracteres
        $user->setResetToken($token);
        $em->flush();

        $this->addFlash('success', "Tu token de recuperación es: $token");
        return $this->redirectToRoute('app_reset_password', ['token' => $token]);
    }

    return $this->render('security/forgot_password.html.twig');
}


    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function resetPassword(
        Request $request,
        string $token,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user) {
            $this->addFlash('error', 'El token no es válido.');
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('password');

            // Actualizar la contraseña
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $user->setResetToken(null); // Limpiar el token después de usarlo
            $em->flush();

            $this->addFlash('success', 'Tu contraseña ha sido restablecida.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', ['token' => $token]);
    }

    #[Route('/recover-account', name: 'app_recover_account')]
    public function recoverAccount(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $usernameOrPhone = $request->request->get('username_or_phone');

            // Buscar por nombre de usuario o teléfono
            $user = $em->getRepository(User::class)->findOneBy(['username' => $usernameOrPhone]) ??
                    $em->getRepository(User::class)->findOneBy(['phone' => $usernameOrPhone]);

            if (!$user) {
                $this->addFlash('error', 'No se encontró ningún usuario con esos datos.');
                return $this->redirectToRoute('app_recover_account');
            }

            $this->addFlash('success', "El correo asociado a tu cuenta es: {$user->getEmail()}");
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/recover_account.html.twig');
    }
}
