<?php

namespace App\Controller\Admin;

use App\Entity\TemplateTest;
use App\Repository\TemplateTestRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class TemplateTestCrudController extends AbstractCrudController
{
    private $templateTestRepository;
    private $adminUrlGenerator;

    public function __construct(TemplateTestRepository $templateTestRepository, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->templateTestRepository = $templateTestRepository;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return TemplateTest::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ///////////////////////////////////////////////////////////////////////Config page index
            ->add(Crud::PAGE_INDEX, Action::DETAIL)

            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setCssClass('text-primary');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setCssClass('text-info');
            })
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fas fa-plus');
            })

            ///////////////////////////////////////////////////////////////////////Config page edit
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fas fa-check');
            })

            ///////////////////////////////////////////////////////////////////////Config page new
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fas fa-check');
            })
            ->reorder(Crud::PAGE_NEW, [Action::SAVE_AND_RETURN])
            ;
            ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Templates de test')
            ->setSearchFields(['nom', 'projet.nom'])
            ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('projet'))
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom')->hideOnDetail(),
            TextareaField::new('description')->hideOnIndex(),
            TextEditorField::new('description')->hideOnForm()->hideOnDetail(),
            DateTimeField::new('dateCreation')->hideOnForm(),
            IntegerField::new('version')->setRequired(false),
            IntegerField::new('position')->setRequired(false),
            AssociationField::new('projet')->autocomplete(),
            AssociationField::new('commentaires')->hideOnForm()->formatValue(function ($value, TemplateTest $templateTest) {

                if($templateTest === 0) return $value;
                else {
                    $url = $this->adminUrlGenerator->unsetAll()
                        ->setController(CommentaireCrudController::class)
                        ->set('filters[templateTest][value]', $templateTest->getId())
                        ->set('filters[templateTest][comparison]', '=')
                        ->generateUrl();
                    return '<a href="' . $url . '">' . $value . '</a>';
                }
            }),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if(!$entityInstance instanceof TemplateTest) return;

        $position = $this->templateTestRepository->createPositionOfTest($entityInstance->getProjet()->getId());

        if ($position === null) {
            $position = 1;
        } else {
            $position = $position['p'] + 1;
        }

        $entityInstance->setVersion(1);
        $entityInstance->setPosition($position);
        $entityInstance->setDateCreation(new \DateTime());

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if(!$entityInstance instanceof TemplateTest) return;

        parent::persistEntity($entityManager, $entityInstance);
    }
}
