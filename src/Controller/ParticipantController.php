<?php

namespace App\Controller;

use App\Entity\Invitation;
use App\Form\AjoutParticipantType;
use App\Repository\DroitRepository;
use App\Repository\ParticipantProjetRepository;
use App\Repository\ProjetRepository;
use App\Repository\RoleRepository;
use App\Repository\UtilisateurRepository;
use App\Service\AutorisationsProjetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/projets/{projetId}/participants", name="participant_")
 */
class ParticipantController extends AbstractController
{
    /**
     * @Route("", name="liste")
     */
    public function liste(
        ProjetRepository           $projetRepository,
        DroitRepository            $droitRepository,
        AutorisationsProjetService $autorisationsProjet,
        UserInterface              $user,
                                   $projetId): Response
    {
        $projet = $projetRepository->findOneById($projetId);

        if ($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRPART']);

            if ($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $participants = $projet->getParticipantsProjet();

                $invitations = $projet->getInvitations();

                return $this->render('participant/liste.html.twig', [
                    'projet' => $projet,
                    'participants' => $participants->getValues(),
                    'invitations' => $invitations
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
        ProjetRepository            $projetRepository,
        UtilisateurRepository       $utilisateurRepository,
        ParticipantProjetRepository $participantProjetRepository,
        DroitRepository             $droitRepository,
        RoleRepository              $roleRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        Request                     $request,
        EntityManagerInterface      $entityManager,
        MailerInterface             $mailer,
        ValidatorInterface          $validator,
                                    $projetId): Response
    {

        $projet = $projetRepository->findOneById($projetId);

        if ($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'AJOUPART']);

            if ($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $form = $this->createForm(AjoutParticipantType::class, null, [
                    'projet' => $projet
                ]);

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $roleId = $form->get('role')->getData();

                    // VERIFICATION DE L'EXISTENCE DU ROLE RENVOYE PAR LE FORMULAIRE
                    $role = $roleRepository->findOneBy(['id' => $roleId, 'projet' => $projet]);

                    if ($role) {
                        $email = $form->get('adresseMail')->getData();

                        // CREATION DE L'INVITATION SI LE ROLE EXISTE
                        $invitation = (new Invitation())
                            ->setAuteur($user)
                            ->setProjet($projet)
                            // Le destinataire est un string pour gérer les utilisateurs non-existants
                            ->setDestinataire($email)
                            ->setRole($role)
                            ->setEtat(Invitation::EN_ATTENTE)
                            ->setDateEnvoi(new \DateTime());

                        // Vérifications des contraintes
                        $errors = $validator->validate($invitation);

                        if ($errors->count() > 0) {
                            // Message d'erreur en cas de contrainte non respectée
                            $this->addFlash('warning', $errors->get(0)->getMessage());

                            return $this->redirectToRoute('participant_liste', ['projetId' => $projet->getId()]);
                        }

                        $utilisateur = $utilisateurRepository->findOneBy(['email' => $email]);

                        $mail = (new TemplatedEmail())
                            ->from('no-reply@namastest.com')
                            ->to($email)
                            ->subject('Vous avez été invité à participer à un projet - ' . $projet->getNom())
                            ->htmlTemplate('emails/invitation-projet.html.twig')
                            ->textTemplate('emails/invitation-projet.txt.twig');

                        if ($utilisateur) {
                            if ($projet->getAuteur() !== $utilisateur) {
                                $participeAuProjet = $participantProjetRepository->findOneBy([
                                    'utilisateur' => $utilisateur,
                                    'projet' => $projet
                                ]);

                                if (!$participeAuProjet) {
                                    $mail->context([
                                        'expediteur' => $user,
                                        'utilisateur' => $utilisateur,
                                        'projet' => $projet,
                                        'role' => $role,
                                    ]);

                                    try {
                                        $mailer->send($mail);

                                        $entityManager->persist($invitation);
                                        $entityManager->flush();

                                        $this->addFlash('success', "L'utilisateur a été invité au projet avec succès.");
                                        return $this->redirectToRoute('participant_liste', ['projetId' => $projet->getId()]);
                                    } catch (TransportExceptionInterface $e) {
                                        $this->addFlash('warning', "Une erreur est survenue lors de l'envoi du mail d'invitation.");
                                    }
                                } else {
                                    $this->addFlash('warning', "L'utilisateur fait déjà partie du projet.");
                                }
                            } else {
                                $this->addFlash('warning', "Vous êtes l'auteur de ce projet.");
                            }
                        } else {
                            $mail->context([
                                'expediteur' => $user,
                                'projet' => $projet,
                                'role' => $role,
                            ]);

                            try {
                                $mailer->send($mail);

                                $entityManager->persist($invitation);
                                $entityManager->flush();

                                $this->addFlash('success', "Un lien pour rejoindre le projet a été envoyé à l'adresse renseignée.");
                                return $this->redirectToRoute('participant_liste', ['projetId' => $projet->getId()]);
                            } catch (TransportExceptionInterface $e) {
                                $this->addFlash('warning', "Une erreur est survenue lors de l'envoi du mail d'invitation.");
                            }
                        }
                    } else {
                        $this->addFlash('danger', "Le rôle que vous essayez d'attribuer à ce participant n'existe pas.");
                    }
                }

                return $this->render('participant/ajouter.html.twig', [
                    'projet' => $projet,
                    'ajoutParticipantForm' => $form->createView(),
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
     * @Route("/{partId}/roles/", name="update_role", methods={"PUT"})
     */
    public function updateRole(ProjetRepository            $projetRepository,
                               ParticipantProjetRepository $participantProjetRepository,
                               DroitRepository             $droitRepository,
                               RoleRepository              $roleRepository,
                               AutorisationsProjetService  $autorisationsProjet,
                               EntityManagerInterface      $entityManager,
                               UserInterface               $user,
                               Request                     $request,
                               int                         $projetId, int $partId): JsonResponse
    {

        $projet = $projetRepository->findOneById($projetId);

        if ($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'EDITPART']);

            if ($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $participant = $participantProjetRepository->findOneBy(['id' => $partId, 'projet' => $projet]);

                if ($participant) {
                    if ($participant->getUtilisateur() !== $user) {
                        $roleId = $request->request->get('role');
                        $role = $roleRepository->findOneBy(['id' => $roleId, 'projet' => $projet]);

                        if ($role) {
                            $participant->setRole($role);

                            $entityManager->persist($participant);
                            $entityManager->flush();

                            return $this->json(['ok' => true]);
                        } else {
                            $errorMsg = "Ce rôle n'existe pas.";
                        }
                    } else {
                        $errorMsg = "Impossible de changer son propre rôle.";
                    }
                } else {
                    $errorMsg = "Ce participant n'existe pas.";
                }
            } else {
                $errorMsg = "Vous ne disposez pas des droits suffisants pour modifier ces données.";
            }
        } else {
            $errorMsg = "Ce projet n'existe pas.";
        }

        return $this->json(['error' => $errorMsg]);
    }


    /**
     * @Route("/supprimer/{partId}", name="supprimer", methods={"DELETE"})
     */
    public function supprimerParticipant(
        ProjetRepository            $projetRepository,
        ParticipantProjetRepository $participantProjetRepository,
        DroitRepository             $droitRepository,
        AutorisationsProjetService  $autorisationsProjet,
        EntityManagerInterface      $entityManager,
        UserInterface               $user,
        int                         $projetId,
        int                         $partId): Response
    {

        $projet = $projetRepository->findOneById($projetId);

        if ($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'SUPPPART']);
            if ($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $participant = $participantProjetRepository->findOneBy(['id' => $partId, 'projet' => $projet]);

                if ($participant) {
                    if ($participant->getUtilisateur() !== $user) {

                            $entityManager->remove($participant);
                            $entityManager->flush();
                        return $this->json(['ok' => true]);
                        }
                } else {
                    $errorMsg = "Le cas de test ciblé n'existe pas.";
                }
            } else {
                $errorMsg = "Vous ne disposez pas des droits suffisants pour supprimer ce test.";
            }
        } else {
            $errorMsg = "Ce projet n'existe pas.";
        }

        return $this->json(['error' => $errorMsg]);
    }

}
