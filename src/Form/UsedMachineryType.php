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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
                'multiple' => false,
                'data' => false
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Tractores' => 'tractor',
                    'Embutidoras' => 'embutidora',
                    'Sembradoras' => 'sembradora',
                    'Equipos de Forraje' => 'equipo de forraje',
                ],
                'label' => 'Categoría',
                'constraints' => [
                    new NotBlank(['message' => 'Debe seleccionar una categoría'])
                ]
            ])
            ->add('machineryName', TextType::class, [
                'label' => 'Nombre de la maquinaria',
                'constraints' => [
                    new NotBlank(['message' => 'El nombre no puede estar vacío'])
                ]
            ])
            ->add('brand', TextType::class, [
                'label' => 'Marca',
                'constraints' => [
                    new NotBlank(['message' => 'La marca no puede estar vacía'])
                ]
            ])
            ->add('price', NumberType::class, [
                'label' => 'Precio',
                'constraints' => [
                    new NotBlank(['message' => 'El precio no puede estar vacío']),
                    new GreaterThan(['value' => 0, 'message' => 'El precio debe ser mayor a 0'])
                ]
            ])
            ->add('imageFilename', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Imagen (opcional)',
            ]);
            
        $builder->add('yearsOld', NumberType::class, [
            'label' => 'Años de antigüedad',
            'required' => true,
            'constraints' => [
                new NotBlank(['message' => 'Los años no pueden estar vacíos']),
                new GreaterThanOrEqual(['value' => 0, 'message' => 'Los años deben ser 0 o mayores'])
            ]
        ])
        ->add('months', NumberType::class, [
            'label' => 'Meses',
            'required' => true,
            'constraints' => [
                new NotBlank(['message' => 'Los meses no pueden estar vacíos']),
                new Range([
                    'min' => 0,
                    'max' => 11,
                    'notInRangeMessage' => 'Los meses deben estar entre {{ min }} y {{ max }}'
                ])
            ]
        ])
        ->add('hoursOfUse', NumberType::class, [
            'label' => 'Horas de uso',
            'required' => true,
            'constraints' => [
                new NotBlank(['message' => 'Las horas de uso no pueden estar vacías']),
                new GreaterThanOrEqual(['value' => 0, 'message' => 'Las horas de uso deben ser 0 o mayores'])
            ]
        ])
        ->add('lastService', DateType::class, [
            'label' => 'Fecha último servicio',
            'required' => true,
            'widget' => 'single_text',
            'data' => new \DateTime(),
            'constraints' => [
                new NotBlank(['message' => 'La fecha de último servicio no puede estar vacía']),
                new LessThanOrEqual(['value' => 'today', 'message' => 'La fecha de último servicio no puede ser posterior al día de hoy'])
            ]
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            
            if (isset($data['isNew']) && ($data['isNew'] === '1' || $data['isNew'] === true)) {
                $data['yearsOld'] = 0;
                $data['months'] = 0;
                $data['hoursOfUse'] = 0;
                $data['lastService'] = (new \DateTime())->format('Y-m-d');
                $event->setData($data);
            }
        });
        
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            
            if ($data->getIsNew()) {
                $data->setYearsOld(0);
                $data->setMonths(0);
                $data->setHoursOfUse(0);
                $data->setLastService(new \DateTime());
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UsedMachinery::class,
        ]);
    }
}