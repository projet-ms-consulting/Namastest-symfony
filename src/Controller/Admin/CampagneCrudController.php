<?php

namespace App\Controller\Admin;

use App\Entity\Campagne;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class CampagneCrudController extends AbstractCrudController
{
    public $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Campagne::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('projet');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom'),
            TextEditorField::new('description')->hideOnForm(),
            TextareaField::new('description')->hideOnIndex(),
            AssociationField::new('projet')->hideOnForm(),
            TextField::new('etat.libelle', 'Etat')->hideOnForm(),
            DateTimeField::new('dateDebutEstimee', 'Date début estimée'),
            DateTimeField::new('dateFinEstimee', 'Date fin estimée'),
            DateTimeField::new('dateDebutReelle', 'Date début réelle'),
            DateTimeField::new('dateFinReelle', 'Date fin réelle'),
            AssociationField::new('tests')->hideOnForm()->formatValue(function ($value, Campagne $campagne) {
                if($value === 0) return $value;
                else {
                    $url = $this->adminUrlGenerator->unsetAll()
                        ->setController(TestCrudController::class)
                        ->set('filters[campagne][value]', $campagne->getId())
                        ->set('filters[campagne][comparison]', '=')
                        ->generateUrl();

                    return '<a href="' . $url . '">' . $value . '</a>';
                }
            }),
            ArrayField::new('data'),
        ];
    }

}
