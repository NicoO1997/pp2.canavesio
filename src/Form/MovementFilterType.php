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
                'label' => 'Repuesto',
                'attr' => [
                    'class' => 'form-control select2'
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.isDeleted = :isDeleted')
                        ->setParameter('isDeleted', false)
                        ->orderBy('p.name', 'ASC');
                }
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Todos los tipos' => '',
                    'Entrada' => ProductMovement::TYPE_ENTRY,
                    'Venta' => ProductMovement::TYPE_SALE,
                    'Eliminación' => ProductMovement::TYPE_DELETION,
                    'Eliminación Permanente' => ProductMovement::TYPE_PERMANENT_DELETE,
                    'Ajuste' => ProductMovement::TYPE_ADJUSTMENT,
                    'Editado' => ProductMovement::TYPE_EDIT
                ],
                'required' => false,
                'label' => 'Tipo de Movimiento',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('dateFrom', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Desde',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'yyyy-mm-dd',
                    'data-date-timezone' => 'UTC'
                ],
                'input' => 'datetime',
                'model_timezone' => 'UTC',
                'view_timezone' => 'UTC'
            ])
            ->add('dateTo', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Hasta',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'yyyy-mm-dd',
                    'data-date-timezone' => 'UTC'
                ],
                'input' => 'datetime',
                'model_timezone' => 'UTC',
                'view_timezone' => 'UTC'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'attr' => [
                'class' => 'filters-form',
                'novalidate' => 'novalidate', // Deshabilita la validación HTML5 del navegador
                'autocomplete' => 'off'
            ],
            'data_class' => null
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'movement_filter'; // Prefijo personalizado para los campos del formulario
    }
}