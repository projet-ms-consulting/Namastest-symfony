<?php

namespace App\Form;

use App\Entity\Campagne;
use App\Entity\TemplateTest;
use App\Repository\CampagneRepository;
use App\Repository\TemplateTestRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampagneChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('campagne', EntityType::class, [
                'class' => Campagne::class,
                'choice_label' => 'nom',
                'label' => 'Campagne',
                'mapped' => false,
                'query_builder' => function (CampagneRepository $cr) use ($options) {
                    return $cr->createQueryBuilder('c')
                        ->andWhere('c.projet = :projet')
                        ->leftJoin('c.etat', 'e')
                        ->andWhere('e.libelle = :libelle')
                        ->setParameter('libelle', $options['etat'])
                        ->setParameter('projet', $options['projet'])
                        ->orderBy('c.nom', 'ASC');
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
                        ->innerJoin('t.templateCatalogueRelation', 'r')
                        ->andWhere('r.catalogue = :cat')
                        ->setParameter('cat', $options['catalogue'])
                        ->orderBy('t.position', 'ASC');
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'projet' => null,
            'catalogue' => null,
            'etat' => null
        ]);
    }
}
