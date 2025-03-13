<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;

#[AsCommand(
    name: 'app:move-product-images',
    description: 'Move product images from old to new location'
)]
class MoveProductImagesCommand extends Command
{
    private $entityManager;
    private $filesystem;
    private $projectDir;

    public function __construct(
        EntityManagerInterface $entityManager,
        string $projectDir
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->filesystem = new Filesystem();
        $this->projectDir = $projectDir;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Define directories using absolute paths
        $oldDir = $this->projectDir . '/assets/imagenes';
        $newDir = $this->projectDir . '/public/images';

        // Verificar que el directorio de origen existe
        if (!$this->filesystem->exists($oldDir)) {
            $io->error(sprintf('El directorio de origen "%s" no existe.', $oldDir));
            return Command::FAILURE;
        }

        // Crear el directorio de destino si no existe
        if (!$this->filesystem->exists($newDir)) {
            $io->note(sprintf('Creando directorio de destino "%s"', $newDir));
            $this->filesystem->mkdir($newDir);
        }

        // Configurar el finder
        $finder = new Finder();
        try {
            $finder->files()->in($oldDir);
        } catch (\InvalidArgumentException $e) {
            $io->error('No se encontraron archivos para mover.');
            return Command::FAILURE;
        }

        if (!$finder->hasResults()) {
            $io->warning('No se encontraron archivos para mover.');
            return Command::SUCCESS;
        }

        $movedFiles = 0;
        $errors = [];

        // Mover archivos y actualizar base de datos
        foreach ($finder as $file) {
            $fileName = $file->getFilename();
            $oldPath = $file->getRealPath();
            $newPath = $newDir . '/' . $fileName;

            try {
                // Mover el archivo
                if ($this->filesystem->exists($oldPath)) {
                    $this->filesystem->rename($oldPath, $newPath, true);
                    
                    // Actualizar las referencias en la base de datos
                    $products = $this->entityManager
                        ->getRepository(Product::class)
                        ->findBy(['image' => 'assets/imagenes/' . $fileName]);
                    
                    foreach ($products as $product) {
                        $product->setImage($fileName);
                    }
                    
                    $movedFiles++;
                    $io->text(sprintf('Archivo movido: %s', $fileName));
                }
            } catch (\Exception $e) {
                $errors[] = sprintf('Error al mover "%s": %s', $fileName, $e->getMessage());
            }
        }

        // Guardar cambios en la base de datos
        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $io->error('Error al actualizar la base de datos: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Mostrar resumen
        if ($movedFiles > 0) {
            $io->success(sprintf('Se movieron %d archivos exitosamente.', $movedFiles));
        }

        if (!empty($errors)) {
            $io->section('Errores encontrados:');
            foreach ($errors as $error) {
                $io->error($error);
            }
        }

        return Command::SUCCESS;
    }
}