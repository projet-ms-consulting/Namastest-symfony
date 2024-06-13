<?php

namespace App\Controller;

use App\Entity\Invitation;
use App\Entity\ParticipantProjet;
use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/invitations", name="invitation_")
 */
class InvitationController extends AbstractController
{
    /**
     * @Route("/{id}/accepter", name="accepter")
     */
    public function accepter(
        UserInterface $user,
        InvitationRepository $invitationRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        $id): Response
    {
        $invitation = $invitationRepository->findOneBy([
            'id' => $id,
            'destinataire' => $user->getUsername(),
            'etat' => Invitation::EN_ATTENTE,
        ]);

        if($invitation) {
            $participationProjet = (new ParticipantProjet())
                ->setRole($invitation->getRole())
                ->setUtilisateur($user)
                ->setProjet($invitation->getProjet())
            ;

            $errors = $validator->validate($participationProjet);

            if($errors->count() > 0) {
                $this->addFlash('warning', $errors->get(0)->getMessage());
            } else {
                $invitation->setEtat(Invitation::ACCEPTEE)
                    ->setDateChoix(new \DateTime());

                $entityManager->persist($participationProjet);
                $entityManager->persist($invitation);
                $entityManager->flush();

                $this->addFlash('success', "Projet rejoint avec succès");
            }
        }

        return $this->redirectToRoute('projet_liste');
    }

    /**
     * @Route("/{id}/refuser", name="refuser")
     */
    public function refuser(
        UserInterface $user,
        InvitationRepository $invitationRepository,
        EntityManagerInterface $entityManager,
        $id): Response
    {
        $invitation = $invitationRepository->findOneBy([
            'id' => $id,
            'destinataire' => $user->getUsername(),
            'etat' => Invitation::EN_ATTENTE,
        ]);

        if($invitation) {
            $invitation->setEtat(Invitation::REFUSEE)
                ->setDateChoix(new \DateTime());

            $entityManager->persist($invitation);
            $entityManager->flush();

            $this->addFlash('danger', "Vous avez refusé l'invitation à ce projet.");
        }

        return $this->redirectToRoute('projet_liste');
    }

    /**
     * Cette fonction permet de supprimer une invitation d'un projet
     *
     * @Route("/{id}/{projetId}/supprimer", name="supprimer")
     */
    public function supprimer(
        InvitationRepository    $invitationRepository,
        EntityManagerInterface  $entityManager,
        int                     $id,
        int                     $projetId
    ): Response
    {
        $invitation = $invitationRepository->findOneBy([
            'id' => $id
        ]);

        if($invitation) {

            $entityManager->remove($invitation);
            $entityManager->flush();

            $this->addFlash('success', "Vous avez correctement supprimé l'invitation");
        }

        return $this->redirectToRoute('participant_liste', ['projetId' => $projetId]);
    }
}
