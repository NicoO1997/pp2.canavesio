<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonthYearSelectorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $months = [
            'Enero' => 1,
            'Febrero' => 2,
            'Marzo' => 3,
            'Abril' => 4,
            'Mayo' => 5,
            'Junio' => 6,
            'Julio' => 7,
            'Agosto' => 8,
            'Septiembre' => 9,
            'Octubre' => 10,
            'Noviembre' => 11,
            'Diciembre' => 12,
        ];

        $currentYear = (int)date('Y');
        $years = range($currentYear, $currentYear - 5);
        $yearChoices = array_combine($years, $years);

        $builder
            ->add('month', ChoiceType::class, [
                'choices' => $months,
                'label' => 'Mes',
                'data' => $options['data']['month'] ?? (int)date('m'),
                'required' => false,
            ])
            ->add('year', ChoiceType::class, [
                'choices' => $yearChoices,
                'label' => 'Año',
                'data' => $options['data']['year'] ?? $currentYear,
                'required' => false,
            ])
            ->add('date_range', ChoiceType::class, [
                'choices' => [
                    'Filtrar por Mes/Año' => 'month_year',
                    'Filtrar por Rango de Fechas' => 'date_range',
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Tipo de Filtro: ',
                'data' => 'month_year',
                'required' => true,
            ])
            ->add('start_date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha Inicio',
                'required' => false,
                'html5' => true,
                'attr' => [
                    'class' => 'date-picker',
                    'max' => (new \DateTime())->format('Y-m-d')
                ],
                'data' => new \DateTime('first day of this month'),
            ])
            ->add('end_date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha Fin',
                'required' => false,
                'html5' => true,
                'attr' => [
                    'class' => 'date-picker',
                    'max' => (new \DateTime())->format('Y-m-d')
                ],
                'data' => new \DateTime('last day of this month'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Consultar',
            ])
            ->add('filter_year', SubmitType::class, [
                'label' => 'Consultar Año',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'method' => 'POST',
            'csrf_protection' => true,
        ]);
    }
}