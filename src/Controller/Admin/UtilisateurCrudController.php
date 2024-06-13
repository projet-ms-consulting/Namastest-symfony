<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class UtilisateurCrudController extends AbstractCrudController
{
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Utilisateur::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Utilisateurs')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ///////////////////////////////////////////////////////////////////////Config page index
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)

            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setCssClass('text-primary');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setCssClass('text-info');
            })

            ///////////////////////////////////////////////////////////////////////Config page detail
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fas fa-edit');
            })
            ->reorder(Crud::PAGE_DETAIL, [Action::EDIT])

            ///////////////////////////////////////////////////////////////////////Config page edit
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fas fa-check');
            })
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nom')->hideOnDetail()->formatValue(function ($value) {
            return strtoupper($value);
        });
        yield TextField::new('prenom', 'Prénom')->hideOnDetail()->formatValue(function ($value) {
            return mb_convert_case($value, MB_CASE_TITLE);
        });
        yield EmailField::new('email');
        yield DateTimeField::new('dateInscription', 'Date d\'inscription')->hideOnForm();

        yield AssociationField::new('projets', 'Auteur de projets')->hideOnForm()->formatValue(function ($value, Utilisateur $utilisateur) {
            if($value === 0) return $value;
            else {
                $url = $this->adminUrlGenerator->unsetAll()
                    ->setController(ProjetCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('filters[auteur][value]', $utilisateur->getId())
                    ->set('filters[auteur][comparison]', '=')
                    ->generateUrl();

                return '<a href="' . $url . '">' . $value . '</a>';
            }
        });

        yield AssociationField::new('participantProjets', 'Participe aux projets')->hideOnForm()->formatValue(function ($value, Utilisateur $utilisateur) {
            if($value === 0) return $value;
            else {
                $url = $this->adminUrlGenerator->unsetAll()
                    ->setController(ParticipantProjetCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('filters[utilisateur][value]', $utilisateur->getId())
                    ->set('filters[utilisateur][comparison]', '=')
                    ->generateUrl();

                return '<a href="' . $url . '">' . $value . '</a>';
            }
        });

        $user = $this->getUser();
        yield BooleanField::new('isAdministrateur', 'Administrateur')->formatValue(function($value, Utilisateur $utilisateur) use ($user) {
            if($user === $utilisateur) {
                $this->addFlash('info', '<i class="fas fa-exclamation-circle"></i> Le champ "Administrateur" est désactivé pour votre compte.');
                return '';
            }
        });
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->addFlash('success', 'L\'utilisateur a bien été modifié !');
        parent::updateEntity($entityManager, $entityInstance);
    }
}
