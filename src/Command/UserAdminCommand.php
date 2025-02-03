<?php

// src/Command/UserAdminCommand.php
namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:user-admin',
    description: 'Gestiona usuarios admin y roles'
)]
class UserAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('action', InputArgument::REQUIRED, 'create-admin o change-role')
            ->addArgument('email', InputArgument::REQUIRED, 'Email del usuario')
            ->addArgument('role', InputArgument::OPTIONAL, 'Rol (solo para change-role)')
            ->addArgument('password', InputArgument::OPTIONAL, 'Contraseña (solo para create-admin)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getArgument('action');
        $email = $input->getArgument('email');

        return match($action) {
            'create-admin' => $this->createAdmin($email, $input->getArgument('password'), $output),
            'change-role' => $this->changeRole($email, $input->getArgument('role'), $output),
            default => Command::FAILURE
        };
    }

    private function createAdmin(string $email, ?string $password, OutputInterface $output): int
    {
        if (!$password) {
            $output->writeln('Se requiere contraseña para crear admin');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('Admin creado exitosamente');
        return Command::SUCCESS;
    }

    private function changeRole(string $email, ?string $role, OutputInterface $output): int
    {
        if (!$role) {
            $output->writeln('Se requiere especificar el rol');
            return Command::FAILURE;
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        
        if (!$user) {
            $output->writeln('Usuario no encontrado');
            return Command::FAILURE;
        }

        $user->setRoles([$role]);
        $this->entityManager->flush();

        $output->writeln('Rol actualizado exitosamente');
        return Command::SUCCESS;
    }
}