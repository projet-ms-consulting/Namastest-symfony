<?php

namespace App\Controller\Admin;

use App\Entity\Catalogue;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CatalogueCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Catalogue::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('projet');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('libelle', 'Libellé')->hideOnDetail(),
            AssociationField::new('projet'),
            AssociationField::new('templateCatalogueRelation'),
        ];
    }
}
