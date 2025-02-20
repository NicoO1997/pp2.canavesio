<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'Estado',
                'required' => false,
                'data' => true,
            ])
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'El nombre no puede estar vacío']),
                ]
            ])
            ->add('quantity', NumberType::class, [
                'required' => true,
                'html5' => true,
                'constraints' => [
                    new NotBlank(['message' => 'La cantidad es obligatoria']),
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'La cantidad no puede ser inferior a cero'
                    ]),
                    new Type([
                        'type' => 'numeric',
                        'message' => 'La cantidad debe ser un número'
                    ])
                ]
            ])
            ->add('price', NumberType::class, [
                'required' => true,
                'html5' => true,
                'constraints' => [
                    new NotBlank(['message' => 'El precio es obligatorio']),
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'El precio no puede ser inferior a cero'
                    ]),
                    new Type([
                        'type' => 'numeric',
                        'message' => 'El precio debe ser un número'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'La descripción no puede estar vacía']),
                ]
            ])
            ->add('brand', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'La marca no puede estar vacía']),
                ]
            ])
            ->add('image', FileType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Debe seleccionar una imagen']),
                ],
                'attr' => [
                    'accept' => 'image/*'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}