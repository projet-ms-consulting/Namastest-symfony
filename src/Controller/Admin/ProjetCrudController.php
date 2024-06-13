<?php

namespace App\Controller\Admin;

use App\Entity\Projet;
use App\Service\EtatsCampagneService;
use App\Service\EtatsTestsService;
use App\Service\RoleGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class ProjetCrudController extends AbstractCrudController
{
    private RoleGeneratorService $roleGeneratorService;
    private EtatsTestsService $etatsTestsService;
    private EtatsCampagneService $etatsCampagneService;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(
        RoleGeneratorService $roleGeneratorService,
        EtatsCampagneService $etatsCampagneService,
        EtatsTestsService $etatsTestsService,
        AdminUrlGenerator $adminUrlGenerator
    )
    {
        $this->roleGeneratorService = $roleGeneratorService;
        $this->etatsCampagneService = $etatsCampagneService;
        $this->etatsTestsService = $etatsTestsService;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Projet::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Projets')
            ->setSearchFields(['nom', 'auteur.nom'])
            ;
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
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('auteur');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom')->hideOnDetail(),
            AssociationField::new('auteur'),
            DateTimeField::new('dateCreation')->hideOnForm(),
            AssociationField::new('templates', 'Templates test')->hideOnForm()->formatValue(function ($value, Projet $projet) {
                if($value === 0) return $value;
                else return '<a href="' . $this->createUrl(TemplateTestCrudController::class, $projet->getId()) . '">' . $value . '</a>';
            }),
            AssociationField::new('catalogues', 'Catalogues')->hideOnForm()->formatValue(function ($value, Projet $projet) {
                if($value === 0) return $value;
                else return '<a href="' . $this->createUrl(CatalogueCrudController::class, $projet->getId()) . '">' . $value . '</a>';
            }),
            AssociationField::new('campagnes', 'Campagnes')->hideOnForm()->formatValue(function ($value, Projet $projet) {
                if($value === 0) return $value;
                else return '<a href="' . $this->createUrl(CampagneCrudController::class, $projet->getId()) . '">' . $value . '</a>';
            }),
            AssociationField::new('participantsProjet', 'Participants')->hideOnForm()->formatValue(function ($value, Projet $projet) {
                if($value === 0) return $value;
                else return '<a href="' . $this->createUrl(ParticipantProjetCrudController::class, $projet->getId()) . '">' . $value . '</a>';
            }),
            AssociationField::new('roles', 'Rôles')->hideOnForm()->formatValue(function ($value, Projet $projet) {
                if($value === 0) return $value;
                else return '<a href="' . $this->createUrl(RoleCrudController::class, $projet->getId()) . '">' . $value . '</a>';
            }),
            AssociationField::new('invitations', 'Invitations')->hideOnForm(),
        ];
    }

    public function createUrl($class, $filterValue): string
    {
        return $this->adminUrlGenerator->unsetAll()
            ->setController($class)
            ->setAction(Action::INDEX)
            ->set('filters[projet][value]', $filterValue)
            ->set('filters[projet][comparison]', '=')
            ->generateUrl();
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if(!$entityInstance instanceof Projet) return;

        $entityInstance->setDateCreation(new \DateTime());

        $this->roleGeneratorService->generate($entityInstance);

        $this->etatsCampagneService->initEtats($entityInstance);
        $this->etatsTestsService->initEtats($entityInstance);

        $this->addFlash('success', 'Le projet a bien été créé !');

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if(!$entityInstance instanceof Projet) return;

        $this->addFlash('success', 'Le projet a bien été modifié !');

        parent::updateEntity($entityManager, $entityInstance);
    }
}
