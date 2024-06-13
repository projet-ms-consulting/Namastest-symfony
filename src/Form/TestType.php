<?php

namespace App\Form;

use App\Entity\TemplateTest;
use App\Entity\Test;
use App\Repository\TemplateTestRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('template', EntityType::class, [
                'class' => TemplateTest::class,
                'choice_label' => 'nom',
                'label' => 'Cas de test parent',
                'required' => false,
                'query_builder' => function (TemplateTestRepository $tr) use ($options) {
                    return $tr->createQueryBuilder('t')
                        ->andWhere('t.projet = :projet')
                        ->setParameter('projet', $options['projet'])
                        ->orderBy('t.nom', 'ASC');
                }
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
