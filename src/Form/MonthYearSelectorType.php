<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ])
            ->add('year', ChoiceType::class, [
                'choices' => $yearChoices,
                'label' => 'Año',
                'data' => $options['data']['year'] ?? $currentYear,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ver Estadísticas',
            ])
            ->add('filter_year', SubmitType::class, [
                'label' => 'Consultar Año',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'method' => 'POST',  // Cambiado de GET a POST
            'csrf_protection' => true,  // Activar protección CSRF para POST
        ]);
    }
}