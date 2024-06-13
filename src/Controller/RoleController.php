<?php

namespace App\Controller;

use App\Entity\Role;
use App\Form\RoleType;
use App\Repository\DroitRepository;
use App\Repository\ParticipantProjetRepository;
use App\Repository\ProjetRepository;
use App\Repository\RoleRepository;
use App\Service\AutorisationsProjetService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/projets/{projetId}/roles", name="role_")
 */
class RoleController extends AbstractController
{
    /**
     * @Route("", name="liste")
     */
    public function liste(ProjetRepository $projetRepository,
        RoleRepository $roleRepository,
        DroitRepository $droitRepository,
        AutorisationsProjetService $autorisationsProjet,
        UserInterface $user,
        $projetId): Response
    {
        $projet = $projetRepository->findOneById($projetId);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRROLE']);

            if ($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $rolesNotInvite = Criteria::create()
                    ->where(Criteria::expr()->neq('libelle', 'Invité'))
                    ->andWhere(Criteria::expr()->eq('projet', $projet));

                // On récupère tous les rôles sauf le rôle "Invité" qui n'est pas modifiable
                $roles = $roleRepository->matching($rolesNotInvite);

                return $this->render('role/liste.html.twig', [
                    'projet' => $projet,
                    'roles' => $roles,
                ]);
            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé(e) à accéder à cette fonctionnalité.");
            }
        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }

    /**
     * @Route("/ajouter", name="ajouter")
     */
    public function ajouter(
        EntityManagerInterface $entityManager,
        ProjetRepository $projetRepository,
        DroitRepository $droitRepository,
        AutorisationsProjetService $autorisationsProjet,
        UserInterface $user,
        Request $request,
        $projetId): Response {

        $projet = $projetRepository->findOneById($projetId);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'AJOUROLE']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $role = (new Role())->setProjet($projet);

                $form = $this->createForm(RoleType::class, $role);
                $form->handleRequest($request);

                if($form->isSubmitted() && $form->isValid()) {
                    $entityManager->persist($role);
                    $entityManager->flush();

                    $this->addFlash('success', "Le rôle ". $role->getLibelle() ." a été ajouté avec succès.");

                    return $this->redirectToRoute('role_liste', [
                        'projetId' => $projet->getId()
                    ]);
                }

                return $this->render('role/ajouter.html.twig', [
                    'projet' => $projet,
                    'roleForm' => $form->createView(),
                ]);
            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé(e) à accéder à cette fonctionnalité.");
            }
        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }

    /**
     * @Route("/{id}/modifier", name="modifier")
     */
    public function modifier(
        EntityManagerInterface $entityManager,
        ProjetRepository $projetRepository,
        RoleRepository $roleRepository,
        DroitRepository $droitRepository,
        AutorisationsProjetService $autorisationsProjet,
        UserInterface $user,
        Request $request,
        $projetId, $id): Response {

        $projet = $projetRepository->findOneById($projetId);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'EDITROLE']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $role = $roleRepository->findOneBy(['id' => $id, 'projet' => $projet]);

                if($role && $role->getLibelle() !== 'Invité') {
                    $form = $this->createForm(RoleType::class, $role);
                    $form->handleRequest($request);

                    if($form->isSubmitted() && $form->isValid()) {
                        $entityManager->persist($role);
                        $entityManager->flush();

                        $this->addFlash('success', "Le rôle a été modifié avec succès.");

                        return $this->redirectToRoute('role_liste', [
                           'projetId' => $projet->getId()
                        ]);
                    }

                    return $this->render('role/modifier.html.twig', [
                        'projet' => $projet,
                        'roleForm' => $form->createView(),
                    ]);
                } else {
                    $this->addFlash('warning', "Le rôle auquel vous essayez d'accéder n'existe pas ou n'est pas modifiable.");
                }
            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé(e) à accéder à cette fonctionnalité.");
            }
        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        EntityManagerInterface $entityManager,
        ProjetRepository $projetRepository,
        RoleRepository $roleRepository,
        DroitRepository $droitRepository,
        ParticipantProjetRepository $participantProjetRepository,
        AutorisationsProjetService $autorisationsProjet,
        UserInterface $user,
        $projetId, $id): JsonResponse {

        $projet = $projetRepository->findOneById($projetId);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'SUPPROLE']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $role = $roleRepository->findOneBy(['id' => $id, 'projet' => $projet]);

                if($role && $role->getLibelle() !== 'Invité') {
                    $participants = $participantProjetRepository->findBy(['role' => $role]);

                    if(count($participants) > 0) {
                        // ON PASSE LES PARTICIPANTS DONT LE ROLE ETAIT CELUI QUE L'ON SUPPRIME EN INVITÉS
                        $invite = $roleRepository->findOneBy(['libelle' => 'Invité']);

                        // SI LE ROLE "Invité" N'EXISTE PAS, ON LE CRÉE
                        if(!$invite) {
                            $invite = (new Role())
                                ->setLibelle('Invité')
                                ->setProjet($projet);

                            $entityManager->persist($invite);
                        }

                        foreach($participants as $p) {
                            $p->setRole($invite);
                            $entityManager->persist($p);
                        }
                    }

                    $entityManager->remove($role);
                    $entityManager->flush();

                    return $this->json(['ok' => true]);
                } else {
                    $errorMsg = "Le rôle ciblé n'existe pas ou n'est pas modifiable.";
                }
            } else {
                $errorMsg = "Vous ne disposez pas des droits suffisants pour supprimer ce rôle.";
            }
        } else {
            $errorMsg = "Ce projet n'existe pas.";
        }

        return $this->json(['error' => $errorMsg]);
    }
}
