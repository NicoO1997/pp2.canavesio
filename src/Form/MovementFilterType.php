<?php
// src/Form/MovementFilterType.php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Product;
use App\Entity\ProductMovement;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class MovementFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Todos los repuestos',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                }
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Todos los tipos' => '',
                    'Entrada' => ProductMovement::TYPE_ENTRY,
                    'Venta' => ProductMovement::TYPE_SALE,
                    'Eliminación' => ProductMovement::TYPE_DELETION,
                    'Ajuste' => ProductMovement::TYPE_ADJUSTMENT,
                    'Editado' => ProductMovement::TYPE_EDIT
                ],
                'required' => false,
            ])
            ->add('dateFrom', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Desde',
                'html5' => true
            ])
            ->add('dateTo', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Hasta',
                'html5' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }
}