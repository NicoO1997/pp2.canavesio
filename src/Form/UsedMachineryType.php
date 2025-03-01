<?php

namespace App\Form;

use App\Entity\UsedMachinery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\All;

class UsedMachineryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isNew', ChoiceType::class, [
                'label' => 'Estado',
                'choices' => [
                    'Nueva' => true,
                    'Usada' => false
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'attr' => ['class' => 'isNew-radio-group']
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Categoría',
                'choices' => [
                    'Tractor' => 'tractor',
                    'Cosechadora' => 'cosechadora',
                    'Sembradora' => 'sembradora',
                    'Pulverizadora' => 'pulverizadora',
                    'Tolva' => 'tolva',
                    'Otro' => 'otro'
                ],
                'placeholder' => 'Seleccione una categoría',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('model', TextType::class, [
                'label' => 'Modelo',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('brand', TextType::class, [
                'label' => 'Marca',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('fuelTankCapacity', NumberType::class, [
                'label' => 'Capacidad del tanque de combustible (litros)',
                'required' => true,
                'scale' => 2,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Positive(['message' => 'El valor debe ser positivo']),
                    new NotBlank(['message' => 'Este campo no puede estar vacío'])
                ]
            ])
            ->add('technology', TextareaType::class, [
                'label' => 'Tecnología',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('transmissionSystem', ChoiceType::class, [
                'label' => 'Sistema de transmisión',
                'choices' => [
                    'Manual' => 'manual',
                    'Automática' => 'automatica',
                    'Hidrostática' => 'hidrostatica',
                    'Mecánica' => 'mecanica',
                    'CVT' => 'cvt',
                    'Otra' => 'otra'
                ],
                'placeholder' => 'Seleccione un sistema',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('location', TextType::class, [
                'label' => 'Ubicación',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('isPriceOnRequest', CheckboxType::class, [
                'label' => 'Precio a consultar',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Precio (USD)',
                'currency' => 'USD',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Positive(['message' => 'El precio debe ser positivo'])
                ]
            ])
            ->add('loadCapacity', NumberType::class, [
                'label' => 'Capacidad de carga (kg)',
                'required' => false,
                'scale' => 2,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Positive(['message' => 'La capacidad de carga debe ser positiva'])
                ]
            ])
            ->add('manufacturingDate', DateType::class, [
                'label' => 'Fecha de fabricación',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control', 'max' => (new \DateTime())->format('Y-m-d')]
            ])
            ->add('hoursOfUse', IntegerType::class, [
                'label' => 'Horas de uso',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 1]
            ])
            ->add('lastService', DateType::class, [
                'label' => 'Último servicio',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control', 'max' => (new \DateTime())->format('Y-m-d')]
            ])
            ->add('imageFilenames', FileType::class, [
                'label' => 'Imágenes',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize' => '5M',
                                'mimeTypes' => [
                                    'image/jpeg',
                                    'image/png',
                                    'image/jpg',
                                ],
                                'mimeTypesMessage' => 'Por favor sube un archivo de imagen válido',
                            ])
                        ]
                    ])
                ],
            ])
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'Habilitar maquinaria',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UsedMachinery::class
        ]);
    }
}