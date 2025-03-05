<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getUserIdentifier(); // Esto usará el email
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%"ROLE_USUARIO"%')
                        ->orderBy('u.email', 'ASC');
                },
                'label' => 'Cliente',
                'placeholder' => 'Seleccione un cliente',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Cantidad',
                'attr' => [
                    'min' => 1,
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notas',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Agregue notas adicionales si es necesario'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}