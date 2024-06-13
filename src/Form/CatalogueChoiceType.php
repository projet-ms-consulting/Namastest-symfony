<?php

namespace App\Form;

use App\Entity\Catalogue;
use App\Repository\CatalogueRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CatalogueChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('catalogue', EntityType::class, [
                'class' => Catalogue::class,
                'choice_label' => 'libelle',
                'label' => 'Catalogue',
                'mapped' => false,
                'query_builder' => function (CatalogueRepository $cr) use ($options) {
                    return $cr->createQueryBuilder('c')
                        ->andWhere('c.projet = :projet')
                        ->setParameter('projet', $options['projet'])
                        ->orderBy('c.libelle', 'ASC');
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'projet' => null
        ]);
    }
}
