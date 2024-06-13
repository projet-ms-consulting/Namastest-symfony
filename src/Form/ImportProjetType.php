<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ImportProjetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('projetCsv', FileType::class, [
                'label' => 'Fichier de sauvegarde (Fichier .csv)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                'required' => true,

                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'text/x-comma-separated-values',
                            'text/comma-separated-values',
                            'application/octet-stream',
                            'application/vnd.ms-excel',
                            'application/x-csv',
                            'text/x-csv',
                            'text/csv',
                            'application/csv',
                            'application/excel',
                            'application/vnd.msexcel',
                            'text/plain',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        ],
                        'mimeTypesMessage' => 'Merci de charger un document .csv',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'mimeTypes' => new MimeTypes()
        ]);
    }
}
