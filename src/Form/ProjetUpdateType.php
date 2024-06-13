<?php

namespace App\Form;

use App\Entity\Catalogue;
use App\Entity\ParticipantProjet;
use App\Entity\Projet;
use App\Entity\Utilisateur;
use App\Repository\CatalogueRepository;
use App\Repository\ParticipantProjetRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('auteur', TextType::class, [
                'mapped' => false
            ])
            ->add('participantsProjet', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'nom',
                'mapped' => false
            ])
            ->add('catalogue', EntityType::class, [
                'class' => Catalogue::class,
                'choice_label' => 'libelle',
                'label' => 'Catalogue',
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
        ]);
    }
}
