<?php

namespace App\Service;

use App\Entity\Droit;
use App\Entity\Projet;
use App\Repository\ParticipantProjetRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class AutorisationsProjetService
{
    private ParticipantProjetRepository $participantProjetRepository;

    public function __construct(
        ParticipantProjetRepository $participantProjetRepository) {

        $this->participantProjetRepository = $participantProjetRepository;
    }

    public function canAccess(
        Projet $projet,
        ?UserInterface $user,
        ?Droit $droit = null): bool {
        // Si l'utilisateur est l'auteur du projet, il peut y accéder
        if($projet->getAuteur() === $user) return true;

        $participeAuProjet = $this->participantProjetRepository->findOneBy([
            'utilisateur' => $user,
            'projet' => $projet
        ]);

        // Si l'utilisateur participe au projet, il peut y accéder
        if($participeAuProjet !== null) {
            if($droit) {
                if($participeAuProjet->getRole()->getDroits()->contains($droit)) return true;
            } else return true;
        }

        return false;
    }
}