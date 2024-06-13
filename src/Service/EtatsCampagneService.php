<?php

namespace App\Service;

use App\Entity\EtatCampagne;
use App\Entity\Projet;
use App\Repository\EtatCampagneRepository;
use Doctrine\ORM\EntityManagerInterface;

class EtatsCampagneService
{
    private $gEtatCampRepository;
    private $gEntityManager;

    public function __construct(EtatCampagneRepository $etatCampagneRepository, EntityManagerInterface $entityManager)
    {
        $this->gEtatCampRepository = $etatCampagneRepository;
        $this->gEntityManager = $entityManager;
    }


    /**
     * @param Projet $projet
     * Cette fonction permet d'initialiser les états des campagnes d'un projet qui vient d'être créé
     */
    public function initEtats(Projet $projet) {
        $etatEnPrep = new EtatCampagne();
        $etatEnPrep->setProjet($projet);
        $etatEnPrep->setLibelle('En Préparation');
        $etatEnPrep->setIsEnPrep(true);
        $etatEnPrep->setIsEnCours(false);
        $etatEnPrep->setIsCloturee(false);

        $this->gEntityManager->persist($etatEnPrep);

        $etatEnCours = new EtatCampagne();
        $etatEnCours->setProjet($projet);
        $etatEnCours->setLibelle('En Cours');
        $etatEnCours->setIsEnPrep(false);
        $etatEnCours->setIsEnCours(true);
        $etatEnCours->setIsCloturee(false);

        $this->gEntityManager->persist($etatEnCours);

        $etatCloture = new EtatCampagne();
        $etatCloture->setProjet($projet);
        $etatCloture->setLibelle('Clôturée');
        $etatCloture->setIsEnPrep(false);
        $etatCloture->setIsEnCours(false);
        $etatCloture->setIsCloturee(true);

        $this->gEntityManager->persist($etatCloture);

        $this->gEntityManager->flush();
    }


    /**
     * @param Projet $projet
     * @return EtatCampagne
     * Cette fonction permet de récupérer l'état en préparation d'un projet
     */
    public function getEtatEnPrep(Projet $projet) : EtatCampagne {
        return $this->gEtatCampRepository->findOneBy(['projet' => $projet, 'isEnPrep' => true]);
    }

    /**
     * @param Projet $projet
     * @return EtatCampagne
     * Cette fonction permet de récupérer l'état en cours d'un projet
     */
    public function getEtatEnCours(Projet $projet) : EtatCampagne {
        return $this->gEtatCampRepository->findOneBy(['projet' => $projet, 'isEnCours' => true]);
    }

    /**
     * @param Projet $projet
     * @return EtatCampagne
     * Cette fonction permet de récupérer l'état clôturé d'un projet
     */
    public function getEtatCloturee(Projet $projet) : EtatCampagne {
        return $this->gEtatCampRepository->findOneBy(['projet' => $projet, 'isCloturee' => true]);
    }

    /**
     * @param string $libelle
     * @param Projet $projet
     * Cette fonction permet de donner un nouveau libellé à l'état en préparation d'un projet
     */
    public function setEtatEnPrep(string $libelle, Projet $projet) {
        $etat = $this->gEtatCampRepository->findOneBy(['projet' => $projet, 'isEnPrep' => true]);
        $etat->setLibelle($libelle);

        $this->gEntityManager->persist($etat);

        $this->gEntityManager->flush();
    }

    /**
     * @param string $libelle
     * @param Projet $projet
     * Cette fonction permet de donner un nouveau libellé à l'état en cours d'un projet
     */
    public function setEtatEnCours(string $libelle, Projet $projet)  {
        $etat =  $this->gEtatCampRepository->findOneBy(['projet' => $projet, 'isEnCours' => true]);
        $etat->setLibelle($libelle);

        $this->gEntityManager->persist($etat);

        $this->gEntityManager->flush();
    }

    /**
     * @param string $libelle
     * @param Projet $projet
     * Cette fonction permet de donner un nouveau libellé à l'état clôturée d'un projet
     */
    public function setEtatCloturee(string $libelle, Projet $projet)  {
        $etat =  $this->gEtatCampRepository->findOneBy(['projet' => $projet, 'isCloturee' => true]);
        $etat->setLibelle($libelle);

        $this->gEntityManager->persist($etat);

        $this->gEntityManager->flush();
    }

}