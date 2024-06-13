<?php

namespace App\Controller\Admin;

use App\Entity\TemplateCatalogueRelation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TemplateCatalogueRelationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TemplateCatalogueRelation::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('template'),
            AssociationField::new('catalogue'),
            IntegerField::new('ordre'),
        ];
    }

}
