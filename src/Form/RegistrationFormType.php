<?php
 
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\Email;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', null, [
            'label' => 'Email',
            'attr' => [
                'placeholder' => 'Email',
                'minlength' => '11',
                'maxlength' => '255'
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Por favor, introduce un email',
                ]),
                new Email([
                    'message' => 'El email "{{ value }}" no es válido.',
                ]),
                new Length([
                    'min' => 11,
                    'max' => 255,
                    'minMessage' => 'El email debe tener al menos {{ limit }} caracteres',
                    'maxMessage' => 'El email no puede superar los {{ limit }} caracteres',
                ])
            ]
        ])
            ->add('username', TextType::class, [
                'label' => 'Nombre de usuario',
                'attr' => [
                    'placeholder' => 'Nombre de usuario',
                    'minlength' => '3',
                    'maxlength' => '10'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'El nombre de usuario no puede estar vacío'
                    ]),
                    new Type([
                        'type' => 'string',
                        'message' => 'El nombre de usuario debe ser texto'
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 10,
                        'minMessage' => 'El nombre de usuario debe tener al menos {{ limit }} caracteres',
                        'maxMessage' => 'El nombre de usuario no puede tener más de {{ limit }} caracteres'
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9]+$/',
                        'message' => 'El nombre de usuario solo puede contener letras y números'
                    ])
                ]
            ])
            ->add('phone', TelType::class, [
                'label' => 'Teléfono',
                'required' => false,
                'attr' => ['placeholder' => 'Teléfono (opcional)'],
            ])
            ->add('securityQuestion', ChoiceType::class, [
                'label' => 'Pregunta clave',
                'choices' => [
                    '¿Cuál es su comida favorita?' => 'comida_favorita',
                    '¿Cuál es el nombre de su mascota?' => 'nombre_mascota',
                    '¿Cuál fue su primer trabajo?' => 'primer_trabajo',
                ],
                'placeholder' => 'Seleccione una pregunta',
            ])
            ->add('securityAnswer', TextType::class, [
                'label' => 'Respuesta a la pregunta clave',
                'required' => true,
                'attr' => ['placeholder' => 'Escriba su respuesta'],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Debe aceptar nuestros términos.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => [
                    'maxlength' => '12',
                    'class' => 'register-input',
                    'placeholder' => 'Contraseña'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, introduzca una contraseña',
                    ]),
                    new Length([
                        'min' => 6,
                        'max' => 12,
                        'minMessage' => 'Su contraseña debe tener al menos {{ limit }} caracteres',
                        'maxMessage' => 'La contraseña no puede superar los {{ limit }} caracteres',
                        'normalizer' => 'trim'
                    ]), 
                    new Callback(function($value, ExecutionContextInterface $context) {
                        if (strlen($value) > 12) {
                            $context->buildViolation('La contraseña no puede superar los 255 caracteres.')
                                ->addViolation();
                        }
                    }),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
