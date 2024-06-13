<?php

namespace App\Form;

use App\Entity\Role;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

class AjoutParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('adresseMail', EmailType::class, [
                'constraints' => [
                    new Email(['message' => 'Adresse mail non valide']),
                ]
            ])
            ->add('role', EntityType::class, [
                'label' => 'Rôle',
                'class' => Role::class,
                'query_builder' => function(EntityRepository $er) use ($options) {
                    return $er
                        ->createQueryBuilder('r')
                        ->andWhere('r.projet = :projet')
                        ->setParameter('projet', $options['projet'])
                    ;
                },
                'choice_label' => 'libelle',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'projet' => null,
        ]);

        $resolver->setAllowedTypes('projet', 'App\Entity\Projet');
    }
}
