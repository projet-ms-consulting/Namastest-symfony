<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;

abstract class AbstractContactReponseCrudController extends AbstractCrudController
{
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ///////////////////////////////////////////////////////////////////////Config page index
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setCssClass('text-info');
            })
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        $propName = get_called_class() === SupportReponseCrudController::class ? 'support' : 'questionGenerale';
        $label = get_called_class() === SupportReponseCrudController::class ? 'Support' : 'Question générale';

        yield AssociationField::new($propName, $label);
        yield DateTimeField::new('dateEnvoi', 'Date d\'envoi');
        yield TextEditorField::new('message');
        yield AssociationField::new('expediteur');

    }
}
