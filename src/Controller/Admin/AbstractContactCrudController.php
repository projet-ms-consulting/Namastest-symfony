<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

abstract class AbstractContactCrudController extends AbstractCrudController
{
    public $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public const ACTION_REPONDRE = 'Répondre';

    public function configureActions(Actions $actions): Actions
    {
        $answer = Action::new(self::ACTION_REPONDRE)
            ->linkToCrudAction('answer');

        return $actions
            ///////////////////////////////////////////////////////////////////////Config page index
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $answer)

            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setCssClass('text-info');
            })
            ->update(Crud::PAGE_INDEX, $answer, function (Action $action) {
                return $action->setLabel(self::ACTION_REPONDRE)->setCssClass('text-primary');
            })

            ///////////////////////////////////////////////////////////////////////Config page detail
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->add(Crud::PAGE_DETAIL, $answer)
            ->update(Crud::PAGE_DETAIL, $answer, function (Action $action) {
                return $action->setIcon('fas fa-reply')->setCssClass('btn btn-info text-white');
            })
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        $propName = get_called_class() === SupportCrudController::class ? 'supportReponses' : 'questionGeneraleReponses';
        $filterName = get_called_class() === SupportCrudController::class ? 'support' : 'questionGenerale';
        $class = get_called_class() === SupportCrudController::class ? SupportReponseCrudController::class : QuestionGeneraleReponseCrudController::class;

        yield TextField::new('fullName', 'De');
        yield EmailField::new('email');
        yield TextField::new('objet');
        yield TextEditorField::new('message');
        yield DateTimeField::new('dateEnvoi', 'Date d\'envoi');
        yield AssociationField::new($propName, 'Réponses')->formatValue(function ($value, $entity) use ($filterName, $class) {

            if($value === 0) return $value;
            else {
                $url = $this->adminUrlGenerator->unsetAll()
                    ->setController($class)
                    ->set('filters[' . $filterName . '][value]', $entity->getId())
                    ->set('filters[' . $filterName . '][comparison]', '=')
                    ->generateUrl();
                return '<a href="' . $url . '">' . $value . '</a>';
            }
        });
        yield BooleanField::new('isArchived', 'Archivé');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(BooleanFilter::new('isArchived'));
    }

}
