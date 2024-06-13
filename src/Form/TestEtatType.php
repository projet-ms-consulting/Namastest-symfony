<?php

namespace App\Form;

use App\Entity\EtatTest;
use App\Entity\Test;
use App\Repository\EtatTestRepository;
use App\Repository\TestRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestEtatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('etat', EntityType::class, [
                'label' => 'Etat',
                'class' => EtatTest::class,
                'choice_label' => 'libelle',
                'query_builder' => function (EtatTestRepository $er) use ($options) {
                    return $er->createQueryBuilder('e')
                        ->where('e.isATester = false')
                        ->andWhere('e.projet = :projet')
                        ->setParameter('projet', $options['projet']);
                }
            ])
            ->add('test', EntityType::class, [
                'class' => Test::class,
                'choice_label' => 'id',
                'label' => 'Tests',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'query_builder' => function (TestRepository $tr) use ($options) {
                    return $tr->createQueryBuilder('t')
                        ->innerJoin('t.campagne', 'c')
                        ->andWhere('c.id = :cam')
                        ->setParameter('cam', $options['campagne'])
                        ->orderBy('t.ordre', 'ASC');
                }
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'campagne' => null,
            'projet' => null
        ]);
    }
}
