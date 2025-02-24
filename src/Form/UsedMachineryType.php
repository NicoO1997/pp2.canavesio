<?php

namespace App\Form;

use App\Entity\UsedMachinery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class UsedMachineryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isNew', ChoiceType::class, [
                'choices' => [
                    'Nueva' => true,
                    'Usada' => false
                ],
                'label' => 'Estado de la maquinaria',
                'required' => true,
                'expanded' => true,
                'multiple' => false
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Tractores' => 'tractor',
                    'Embutidoras' => 'embutidora',
                    'Sembradoras' => 'sembradora',
                    'Equipos de Forraje' => 'equipo de forraje',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Debe seleccionar una categoría'])
                ]
            ])
            ->add('machineryName', null, [
                'constraints' => [
                    new NotBlank(['message' => 'El nombre no puede estar vacío'])
                ]
            ])
            ->add('brand', null, [
                'constraints' => [
                    new NotBlank(['message' => 'La marca no puede estar vacía'])
                ]
            ])
            ->add('price', NumberType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'El precio no puede estar vacío']),
                    new GreaterThan(['value' => 0, 'message' => 'El precio debe ser mayor a 0'])
                ]
            ])
            ->add('hoursOfUse', NumberType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Las horas de uso no pueden estar vacías']),
                    new GreaterThanOrEqual(['value' => 0, 'message' => 'Las horas de uso deben ser 0 o mayores'])
                ]
            ])
            ->add('months', NumberType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Los meses no pueden estar vacíos']),
                    new Range([
                        'min' => 1,
                        'max' => 11,
                        'notInRangeMessage' => 'Los meses deben estar entre {{ min }} y {{ max }}'
                    ])
                ]
            ])
            ->add('yearsOld', NumberType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Los años no pueden estar vacíos']),
                    new GreaterThan(['value' => 0, 'message' => 'Los años deben ser mayores a 0'])
                ]
            ])
            ->add('lastService', DateType::class, [
                'required' => true,
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'La fecha de último servicio no puede estar vacía']),
                    new LessThanOrEqual(['value' => 'today', 'message' => 'La fecha de último servicio no puede ser posterior al día de hoy'])
                ]
            ])
            ->add('imageFilename', FileType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Debe seleccionar una imagen'])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UsedMachinery::class,
        ]);
    }
}