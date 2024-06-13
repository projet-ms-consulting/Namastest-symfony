<?php

namespace App\Controller\Admin;

use App\Entity\Campagne;
use App\Entity\Catalogue;
use App\Entity\Commentaire;
use App\Entity\EtatTest;
use App\Entity\ParticipantProjet;
use App\Entity\Projet;
use App\Entity\QuestionGenerale;
use App\Entity\QuestionGeneraleReponse;
use App\Entity\Role;
use App\Entity\Support;
use App\Entity\SupportReponse;
use App\Entity\TemplateCatalogueRelation;
use App\Entity\TemplateTest;
use App\Entity\Test;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    private AdminUrlGenerator $adminUrlGenerator;
    private UtilisateurRepository $repository;

    public function __construct(
        AdminUrlGenerator $adminUrlGenerator,
        UtilisateurRepository $repository)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->repository = $repository;
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        $users = $this->repository->findAll();

        return $this->render('admin/index.html.twig', [
            'users' => $users
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Accueil administration');
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->update(Crud::PAGE_INDEX, Action::BATCH_DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash-o');
            })
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fas fa-edit');
            })
            ->reorder(Crud::PAGE_DETAIL, [Action::EDIT])
            ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Retour au site', 'fas fa-home', '/projets/liste');

        yield MenuItem::section('Généralités', 'fas fa-desktop');

        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', Utilisateur::class);

        yield MenuItem::linkToCrud('Projets', 'fas fa-project-diagram', Projet::class);

        yield MenuItem::subMenu('(en test)', 'fas fa-wrench')->setSubItems([
            MenuItem::linkToCrud('Campagnes', 'fas fa-receipt', Campagne::class),
            MenuItem::linkToCrud('Catalogues', 'fas fa-folder-open', Catalogue::class),
            MenuItem::linkToCrud('Templates tests', 'fas fa-list', TemplateTest::class),
            MenuItem::linkToCrud('Templates-Catalogues relations', 'fas fa-list', TemplateCatalogueRelation::class),
            MenuItem::linkToCrud('Tests', 'far fa-list-alt', Test::class),
            MenuItem::linkToCrud('Commentaires', 'fas fa-comment', Commentaire::class),
            MenuItem::linkToCrud('Participants', 'fas fa-users', ParticipantProjet::class),
            MenuItem::linkToCrud('Rôles', 'fas fa-user-shield', Role::class),
        ]);

        yield MenuItem::section('Messages', 'fas fa-envelope');
        yield MenuItem::subMenu('Support technique', 'fas fa-cogs')->setSubItems([
            MenuItem::linkToCrud('Liste', 'fas fa-list', Support::class)
                ->setQueryParameter('filters[isArchived]', 0),
            MenuItem::linkToCrud('Réponses', 'fas fa-reply', SupportReponse::class),
            MenuItem::linkToCrud('Archives', 'fas fa-box', Support::class)
                ->setQueryParameter('filters[isArchived]', 1),
        ]);
        yield MenuItem::subMenu('Questions générales', 'fas fa-question')->setSubItems([
            MenuItem::linkToCrud('Liste', 'fas fa-list', QuestionGenerale::class)
                ->setQueryParameter('filters[isArchived]', 0),
            MenuItem::linkToCrud('Réponses', 'fas fa-reply', QuestionGeneraleReponse::class),
            MenuItem::linkToCrud('Archives', 'fas fa-box', QuestionGenerale::class)
                ->setQueryParameter('filters[isArchived]', 1),
        ]);

        yield MenuItem::section('Blog', 'fas fa-blog');
    }
}
