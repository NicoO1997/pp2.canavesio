<?php

namespace App\Form;

use App\Entity\UsedMachinery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\FileType;



class UsedMachineryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categoryMap = [
            'Tractores' => 'tractor',
            'Embutidoras' => 'embutidora',
            'Sembradoras' => 'sembradora',
            'Equipos de Forraje' => 'equipo de forraje',
        ];
        $builder
            ->add('machineryName')
            ->add('brand')
            ->add('yearsOld')
            ->add('months')
            ->add('hoursOfUse')
            ->add('lastService', null, [
                'widget' => 'single_text',
            ])
            ->add('price')
            ->add('category', ChoiceType::class, [
                'choices' => $categoryMap,
                'required' => true,
                'placeholder' => 'Seleccione una categoría',
                'constraints' => [
                    new NotBlank(['message' => 'La categoría es obligatoria']),
                ]
            ])
            ->add('imageFilename', FileType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UsedMachinery::class,
        ]);
    }
}
