<?php

namespace App\Form;

use App\Entity\EtatTest;
use App\Entity\Test;
use App\Repository\EtatTestRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestResultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('etat', EntityType::class, [
                'label' => 'Etat',
                'class' => EtatTest::class,
                'choice_label' => 'libelle',
                'query_builder' => function (EtatTestRepository $tr) use ($options) {
                    return $tr->createQueryBuilder('e')
                        ->andWhere('e.projet = :projet')
                        ->setParameter('projet', $options['projet']);
                }
            ])
            ->add('precisionsResultat', TextareaType::class, [
                'label' => 'Précisions',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Test::class,
            'projet' => null
        ]);
    }
}
