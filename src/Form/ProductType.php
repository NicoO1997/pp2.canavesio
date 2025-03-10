<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Bundle\SecurityBundle\Security;
use App\Service\SparePartsBrandsService;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;



class ProductType extends AbstractType
{
    private $security;
    private $formModifier;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $allBrands = [];
        foreach ($this->getSparePartsBrands() as $brands) {
            $allBrands = array_merge($allBrands, $brands);
        }
        $allBrands = array_unique($allBrands);
        sort($allBrands);
        $builder
            // Sección de Identificación del Repuesto
            ->add('name', TextType::class, [
                'label' => 'Nombre del repuesto',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ej: Filtro de aire, Cuchilla, Disco de siembra',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'El nombre no puede estar vacío']),
                ]
            ])
            ->add('partNumber', TextType::class, [
                'label' => 'Código o número de parte',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ingrese el código único del fabricante',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'El código de parte es obligatorio']),
                ]
            ])
            ->add('compatibleModels', TextareaType::class, [
                'label' => 'Modelos compatibles',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Liste los modelos de máquinas compatibles',
                    'class' => 'form-control',
                    'rows' => 3
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Debe especificar los modelos compatibles']),
                ]
            ])

            // Sección de Dimensiones y Materiales
            ->add('dimensions', TextType::class, [
                'label' => 'Dimensiones',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ej: 100 x 50 x 25',
                    'class' => 'form-control dimensions-input',
                ]
            ])
            ->add('material', TextType::class, [
                'label' => 'Material',
                'required' => true,
                'attr' => [
                    'class' => 'form-control material-input',
                    'placeholder' => 'Ingrese el material',
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'El material no puede estar vacío']),
                ]
            ])
            ->add('weight', NumberType::class, [
                'label' => 'Peso (kg)',
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'placeholder' => 'Ingrese el peso en kilogramos',
                    'class' => 'form-control'
                ]
            ])

            // ->add('loadCapacity', TextareaType::class, [
            //     'label' => 'Resistencia a la carga',
            //     'required' => false,
            //     'attr' => [
            //         'placeholder' => 'Describa la capacidad de carga o presión soportada',
            //         'class' => 'form-control',
            //         'rows' => 2
            //     ]
            // ])
            ->add('estimatedLifeHours', NumberType::class, [
                'label' => 'Vida útil estimada (meses)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ingrese los meses de uso estimados',
                    'class' => 'form-control'
                ]
            ])

            // ->add('partType', ChoiceType::class, [
            //     'label' => 'Tipo de Repuesto',
            //     'choices' => [
            //         'Seleccione el tipo de repuesto' => '',
            //         'Repuestos para Tractores' => [
            //             'Filtros de aire' => 'tractor_filtro_aire',
            //             'Filtros de aceite' => 'tractor_filtro_aceite',
            //             'Filtros de combustible' => 'tractor_filtro_combustible',
            //             'Filtros hidráulicos' => 'tractor_filtro_hidraulico',
            //             'Correas de transmisión' => 'tractor_correa_transmision',
            //             'Correas de alternador' => 'tractor_correa_alternador',
            //             'Baterías' => 'tractor_bateria',
            //             'Alternadores' => 'tractor_alternador',
            //             'Motores de arranque' => 'tractor_motor_arranque',
            //             'Fusibles' => 'tractor_fusible',
            //             'Neumáticos' => 'tractor_neumatico',
            //             'Llantas' => 'tractor_llanta',
            //             'Pistones' => 'tractor_piston',
            //             'Bujías' => 'tractor_bujia',
            //             'Inyectores' => 'tractor_inyector',
            //             'Radiadores' => 'tractor_radiador',
            //             'Bombas hidráulicas' => 'tractor_bomba_hidraulica',
            //             'Mangueras hidráulicas' => 'tractor_manguera_hidraulica',
            //             'Válvulas hidráulicas' => 'tractor_valvula_hidraulica'
            //         ],
            //         'Repuestos para Cosechadoras' => [
            //             'Cuchillas para trigo' => 'cosechadora_cuchilla_trigo',
            //             'Cuchillas para maíz' => 'cosechadora_cuchilla_maiz',
            //             'Cuchillas para arroz' => 'cosechadora_cuchilla_arroz',
            //             'Tamices' => 'cosechadora_tamiz',
            //             'Zarandas' => 'cosechadora_zaranda',
            //             'Correas de transmisión' => 'cosechadora_correa',
            //             'Cadenas de transmisión' => 'cosechadora_cadena',
            //             'Rotores' => 'cosechadora_rotor',
            //             'Sacudidores' => 'cosechadora_sacudidor'
            //         ],
            //         'Repuestos para Equipos de Siembra' => [
            //             'Discos de siembra' => 'siembra_disco',
            //             'Cuchillas de siembra' => 'siembra_cuchilla',
            //             'Dosificadores de semillas' => 'siembra_dosificador',
            //             'Tubos de fertilización' => 'siembra_tubo',
            //             'Boquillas de fertilización' => 'siembra_boquilla',
            //             'Rodamientos' => 'siembra_rodamiento',
            //             'Ejes' => 'siembra_eje'
            //         ],
            //         'Repuestos para Equipos de Riego' => [
            //             'Aspersores' => 'riego_aspersor',
            //             'Boquillas de riego' => 'riego_boquilla',
            //             'Mangueras' => 'riego_manguera',
            //             'Tuberías' => 'riego_tuberia',
            //             'Bombas de agua' => 'riego_bomba',
            //             'Filtros de riego' => 'riego_filtro'
            //         ],
            //         'Repuestos para Equipos de Forraje' => [
            //             'Cuchillas para forraje' => 'forraje_cuchilla',
            //             'Martillos picadores' => 'forraje_martillo',
            //             'Barras de corte' => 'forraje_barra',
            //             'Púas para segadoras' => 'forraje_pua_segadora',
            //             'Púas para empacadoras' => 'forraje_pua_empacadora',
            //             'Cadenas' => 'forraje_cadena',
            //             'Engranajes' => 'forraje_engranaje'
            //         ]
            //     ],
            //     'required' => true,
            //     'attr' => [
            //         'class' => 'form-control part-type-select',
            //         'data-live-search' => 'true'
            //     ],
            //     'group_by' => function($choice, $key, $value) {
            //         if (strpos($value, 'tractor_') === 0) return 'Repuestos para Tractores';
            //         if (strpos($value, 'cosechadora_') === 0) return 'Repuestos para Cosechadoras';
            //         if (strpos($value, 'siembra_') === 0) return 'Repuestos para Equipos de Siembra';
            //         if (strpos($value, 'riego_') === 0) return 'Repuestos para Equipos de Riego';
            //         if (strpos($value, 'forraje_') === 0) return 'Repuestos para Equipos de Forraje';
            //         return 'Otros';
            //     }
            // ])
            ->add('installationRequirements', TextareaType::class, [
                'label' => 'Requisitos de instalación',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Especifique herramientas necesarias y procedimientos especiales',
                    'class' => 'form-control',
                    'rows' => 3
                ]
            ])

            // Campos existentes del producto
            ->add('quantity', NumberType::class, [
                'label' => 'Cantidad en stock',
                'required' => true,
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La cantidad es obligatoria']),
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'La cantidad no puede ser inferior a cero'
                    ]),
                    new Type([
                        'type' => 'numeric',
                        'message' => 'La cantidad debe ser un número'
                    ])
                ]
            ])
            ->add('price', NumberType::class, [
                'label' => 'Precio',
                'required' => true,
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'constraints' => [
                    new NotBlank(['message' => 'El precio es obligatorio']),
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'El precio no puede ser inferior a cero'
                    ]),
                    new Type([
                        'type' => 'numeric',
                        'message' => 'El precio debe ser un número'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción general',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Descripción detallada del repuesto'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La descripción no puede estar vacía']),
                ]
            ])

            ->add('brand', TextType::class, [
                'label' => 'Marca',
                'required' => true,
                'attr' => [
                    'class' => 'form-control brand-input',
                    'placeholder' => 'Ingrese la marca del repuesto',
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La marca no puede estar vacía']),
                ]
            ])
            // Cambiar FileType a TextareaType para manejar JSON
            ->add('image', FileType::class, [
                'label' => 'Imagen del repuesto',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Por favor sube una imagen válida (JPEG, PNG, WEBP)',
                    ])
                ]
            ])
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'Habilitar repuesto',
                'required' => false,
                'data' => true,
                'attr' => ['class' => 'form-check-input']
            ]);

        // Solo agregar minStock si el usuario tiene el rol ROLE_GESTORSTOCK
        if ($this->security->isGranted('ROLE_GESTORSTOCK')) {
            $builder->add('minStock', NumberType::class, [
                'label' => 'Stock mínimo',
                'required' => true,
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'constraints' => [
                    new NotBlank(['message' => 'El stock mínimo es obligatorio']),
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'El stock mínimo no puede ser inferior a cero'
                    ]),
                    new Type([
                        'type' => 'numeric',
                        'message' => 'El stock mínimo debe ser un número'
                    ])
                ]
            ]);
        }
    }

    private function getSparePartsBrands(): array
    {
        return [
            'tractor_filtro_aire' => ['Mann+Hummel', 'Fleetguard', 'Donaldson', 'Baldwin', 'WIX', 'FRAM', 'Mahle', 'Luber-finer', 'Parker Filtration', 'K&N'],
        'tractor_filtro_aceite' => ['Mann+Hummel', 'Fleetguard', 'WIX', 'FRAM', 'Mahle', 'Baldwin', 'Donaldson', 'Luber-finer', 'Bosch', 'ACDelco'],
        'tractor_filtro_combustible' => ['Fleetguard', 'Mann+Hummel', 'Donaldson', 'Baldwin', 'Bosch', 'Delphi', 'Parker', 'WIX', 'FRAM', 'Mahle'],
        'tractor_filtro_hidraulico' => ['Hydac', 'Parker', 'Donaldson', 'Mann+Hummel', 'Fleetguard', 'Baldwin', 'Bosch Rexroth', 'MP Filtri', 'Mahle', 'Pall'],

        // Repuestos para Tractores - Correas
        'tractor_correa_transmision' => ['Gates', 'Continental', 'Dayco', 'Goodyear', 'Mitsuboshi', 'Optibelt', 'Bando', 'SKF', 'Jason', 'PIX'],
        'tractor_correa_alternador' => ['Gates', 'Dayco', 'Continental', 'Bosch', 'SKF', 'Mitsuboshi', 'Goodyear', 'Bando', 'Optibelt', 'PIX'],

        // Repuestos para Tractores - Sistema Eléctrico
        'tractor_bateria' => ['Bosch', 'Varta', 'Exide', 'Optima', 'ACDelco', 'Interstate', 'Deka', 'Yuasa', 'Banner', 'Odyssey'],
        'tractor_alternador' => ['Bosch', 'Denso', 'Delco Remy', 'Prestolite', 'Valeo', 'Mitsubishi Electric', 'Hitachi', 'Lucas', 'Nikko', 'Sawafuji'],
        'tractor_motor_arranque' => ['Bosch', 'Denso', 'Delco Remy', 'Valeo', 'Prestolite', 'Mitsubishi Electric', 'Hitachi', 'Lucas', 'Nikko', 'WAI'],
        'tractor_fusible' => ['Littelfuse', 'Bussmann', 'Cooper Bussmann', 'Ferraz Shawmut', 'Schneider Electric', 'ABB', 'Siemens', 'Phoenix Contact', 'TE Connectivity', 'Mersen'],

        // Repuestos para Tractores - Neumáticos y Llantas
        'tractor_neumatico' => ['Firestone', 'Michelin', 'Goodyear', 'BKT', 'Alliance', 'Continental', 'Trelleborg', 'Titan', 'Mitas', 'Bridgestone'],
        'tractor_llanta' => ['Titan', 'GKN Wheels', 'Accuride', 'Maxion Wheels', 'Pronar', 'Birrana', 'Stalker', 'Grasdorf', 'Starco', 'Wheel Works'],

        // Repuestos para Tractores - Motor
        'tractor_piston' => ['Mahle', 'Federal-Mogul', 'NPR', 'Nural', 'Sealed Power', 'DNJ Engine Components', 'IPD', 'United Engine & Machine', 'Wiseco', 'Ross Racing Pistons'],
        'tractor_bujia' => ['NGK', 'Bosch', 'Denso', 'Champion', 'ACDelco', 'Motorcraft', 'Autolite', 'BERU', 'Splitfire', 'E3'],
        'tractor_inyector' => ['Bosch', 'Delphi', 'Denso', 'Continental/VDO', 'Siemens/VDO', 'Stanadyne', 'Yanmar', 'Zexel', 'Lucas', 'Caterpillar'],
        'tractor_radiador' => ['Modine', 'Valeo', 'Denso', 'Nissens', 'Behr', 'CSF', 'TYC', 'APDI', 'Spectra Premium', 'Vista-Pro'],

        // Repuestos para Tractores - Sistema Hidráulico
        'tractor_bomba_hidraulica' => ['Parker', 'Bosch Rexroth', 'Eaton', 'Danfoss', 'Casappa', 'Bondioli & Pavesi', 'Sauer-Danfoss', 'Commercial', 'Prince', 'Cross'],
        'tractor_manguera_hidraulica' => ['Parker', 'Gates', 'Eaton', 'Bridgestone', 'Continental', 'Manuli', 'Alfagomma', 'Semperit', 'Dunlop', 'Goodyear'],
        'tractor_valvula_hidraulica' => ['Parker', 'Bosch Rexroth', 'Eaton', 'Danfoss', 'Sun Hydraulics', 'Hydac', 'Walvoil', 'Prince', 'Cross', 'Brand'],

        // Repuestos para Cosechadoras
        'cosechadora_cuchilla_trigo' => ['John Deere', 'Case IH', 'New Holland', 'Claas', 'MacDon', 'Massey Ferguson', 'Deutz-Fahr', 'Laverda', 'Gleaner', 'Fendt'],
        'cosechadora_cuchilla_maiz' => ['John Deere', 'Case IH', 'New Holland', 'Claas', 'Capello', 'Olimac', 'Geringhoff', 'Drago', 'MacDon', 'Fantini'],
        'cosechadora_cuchilla_arroz' => ['John Deere', 'Case IH', 'New Holland', 'Claas', 'MacDon', 'Massey Ferguson', 'Kubota', 'Yanmar', 'ISEKI', 'Mitsubishi'],
        'cosechadora_tamiz' => ['John Deere', 'Case IH', 'New Holland', 'Claas', 'Massey Ferguson', 'Deutz-Fahr', 'AGCO', 'Laverda', 'Gleaner', 'Challenger'],
        'cosechadora_zaranda' => ['John Deere', 'Case IH', 'New Holland', 'Claas', 'Massey Ferguson', 'Deutz-Fahr', 'AGCO', 'Laverda', 'Gleaner', 'Challenger'],
        'cosechadora_correa' => ['Gates', 'Optibelt', 'Continental', 'Dayco', 'Goodyear', 'Mitsuboshi', 'Bando', 'SKF', 'Jason', 'PIX'],
        'cosechadora_cadena' => ['Regina', 'Diamond Chain', 'Renold', 'Tsubaki', 'IWIS', 'RK', 'DID', 'KMC', 'Donghua', 'Timken'],
        'cosechadora_rotor' => ['John Deere', 'Case IH', 'New Holland', 'Claas', 'Massey Ferguson', 'Deutz-Fahr', 'AGCO', 'Laverda', 'Gleaner', 'Challenger'],
        'cosechadora_sacudidor' => ['John Deere', 'Case IH', 'New Holland', 'Claas', 'Massey Ferguson', 'Deutz-Fahr', 'AGCO', 'Laverda', 'Gleaner', 'Challenger'],

        // Repuestos para Equipos de Siembra
        'siembra_disco' => ['John Deere', 'Precision Planting', 'Kinze', 'Great Plains', 'Case IH', 'Monosem', 'Amazone', 'Lemken', 'Väderstad', 'Semeato'],
        'siembra_cuchilla' => ['Bellota Agrisolutions', 'Ingersoll', 'John Deere', 'Case IH', 'Kinze', 'Great Plains', 'Horsch', 'Amazone', 'Kuhn', 'Lemken'],
        'siembra_dosificador' => ['Precision Planting', 'John Deere', 'Case IH', 'Kinze', 'Great Plains', 'Monosem', 'Amazone', 'Horsch', 'Kuhn', 'MaterMacc'],
        'siembra_tubo' => ['John Deere', 'Case IH', 'Kinze', 'Great Plains', 'Monosem', 'Amazone', 'Lemken', 'Horsch', 'Kuhn', 'Semeato'],
        'siembra_boquilla' => ['TeeJet', 'Hypro', 'Arag', 'Lechler', 'Albuz', 'Hardi', 'John Deere', 'Case IH', 'Amazone', 'Kuhn'],
        'siembra_rodamiento' => ['SKF', 'Timken', 'NTN', 'NSK', 'FAG', 'INA', 'Koyo', 'NACHI', 'FYH', 'NKE'],
        'siembra_eje' => ['John Deere', 'Case IH', 'Kinze', 'Great Plains', 'SKF', 'Timken', 'NTN', 'NSK', 'FAG', 'INA'],

        // Repuestos para Equipos de Riego
        'riego_aspersor' => ['Nelson', 'Rain Bird', 'Hunter', 'Senninger', 'Komet', 'Netafim', 'Valley', 'Lindsay', 'Reinke', 'T-L'],
        'riego_boquilla' => ['Nelson', 'TeeJet', 'Senninger', 'Rain Bird', 'Hunter', 'Komet', 'Netafim', 'Hypro', 'Arag', 'Lechler'],
        'riego_manguera' => ['Netafim', 'John Deere Water', 'Rain Bird', 'Hunter', 'Toro', 'Irritec', 'NaanDanJain', 'Rivulis', 'Jain', 'Plastro'],
        'riego_tuberia' => ['Charlotte Pipe', 'JM Eagle', 'Netafim', 'Rain Bird', 'Hunter', 'Irritec', 'NaanDanJain', 'Rivulis', 'Jain', 'Georg Fischer'],
        'riego_bomba' => ['Grundfos', 'Xylem', 'KSB', 'Wilo', 'Franklin Electric', 'Berkeley', 'Cornell', 'Pentair', 'Goulds', 'Caprari'],
        'riego_filtro' => ['Amiad', 'Netafim', 'Arkal', 'Rain Bird', 'Hunter', 'Azud', 'STF', 'Hydro-Flow', 'Spin Klin', 'Sand Master'],

        // Repuestos para Equipos de Forraje
        'forraje_cuchilla' => ['John Deere', 'New Holland', 'Case IH', 'Claas', 'Krone', 'Kuhn', 'Pöttinger', 'Vicon', 'Deutz-Fahr', 'Fella'],
        'forraje_martillo' => ['John Deere', 'New Holland', 'Case IH', 'Claas', 'Krone', 'Kuhn', 'Maschio', 'Pöttinger', 'Vogel & Noot', 'Seppi'],
        'forraje_barra' => ['John Deere', 'New Holland', 'Case IH', 'Claas', 'Krone', 'Kuhn', 'Pöttinger', 'Vicon', 'Deutz-Fahr', 'Fella'],
        'forraje_pua_segadora' => ['John Deere', 'New Holland', 'Case IH', 'Claas', 'Krone', 'Kuhn', 'Pöttinger', 'Vicon', 'Deutz-Fahr', 'Fella'],
        'forraje_pua_empacadora' => ['John Deere', 'New Holland', 'Case IH', 'Claas', 'Krone', 'Kuhn', 'Massey Ferguson', 'Deutz-Fahr', 'Welger', 'Vicon'],
        'forraje_cadena' => ['Regina', 'Diamond Chain', 'Renold', 'Tsubaki', 'IWIS', 'RK', 'DID', 'KMC', 'Donghua', 'Timken'],
        'forraje_engranaje' => ['Martin Sprocket', 'Boston Gear', 'QTC Metric Gears', 'Browning', 'Hub City', 'Rexnord', 'Dodge', 'Baldor', 'SEW-Eurodrive', 'Nord']
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}