<?php

namespace App\Controller\Admin;

use App\Entity\ParticipantProjet;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ParticipantProjetCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ParticipantProjet::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('utilisateur')
            ->add('projet')
            ;
    }

    public function configureFields(string $pageName): iterable
    {


        return  [
            AssociationField::new('utilisateur'),
            AssociationField::new('projet'),
            AssociationField::new('role'),

        ];
    }

}
