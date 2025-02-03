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
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'El nombre es obligatorio']),
                ]
            ])
            ->add('quantity', NumberType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'La cantidad es obligatoria']),
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'La cantidad debe ser mayor a 0'
                    ])
                ]
            ])
            ->add('price', NumberType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'El precio es obligatorio']),
                    new GreaterThan([
                        'value' => 0,
                        'message' => 'El precio debe ser mayor a 0'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'La descripciÃ³n es obligatoria']),
                ]
            ])
            ->add('brand', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'La marca es obligatoria']),
                ]
            ])
            ->add('image', FileType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'La imagen es obligatoria']),
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
