<?php

namespace App\Form;

use App\Entity\Catalogue;
use App\Entity\TemplateTest;
use App\Repository\CatalogueRepository;
use App\Repository\TemplateTestRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateTestAndCatalogueChoiceBoxesType extends AbstractType
{

    // Ce formulaire contient deux entités afin de permettre à l'utilisateur de d'ajouter un nombre illimité de tests
    // à un nombre illimité de catalogue en cliquant sur un bouton unique
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('catalogue', EntityType::class, [
                'class' => Catalogue::class,
                'choice_label' => 'libelle',
                'label' => 'Catalogues',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'query_builder' => function (CatalogueRepository $cr) use ($options) {
                    return $cr->createQueryBuilder('c')
                        ->andWhere('c.projet = :projet')
                        ->setParameter('projet', $options['projet'])
                        ->orderBy('c.libelle', 'ASC');
                }
            ])
            ->add('test', EntityType::class, [
                'class' => TemplateTest::class,
                'choice_label' => 'nom',
                'label' => 'Tests',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'query_builder' => function (TemplateTestRepository $tr) use ($options) {
                    return $tr->createQueryBuilder('t')
                        ->andWhere('t.projet = :projet')
                        ->setParameter('projet', $options['projet'])
                        ->orderBy('t.position', 'ASC');
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
