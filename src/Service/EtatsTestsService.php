<?php

namespace App\Service;

use App\Entity\EtatTest;
use App\Entity\Projet;
use App\Repository\EtatTestRepository;
use Doctrine\ORM\EntityManagerInterface;

class EtatsTestsService
{
    private $gEtatTestRepository;
    private $gEntityManager;

    public function __construct(EtatTestRepository $etatTestRepository, EntityManagerInterface $entityManager)
    {
        $this->gEtatTestRepository = $etatTestRepository;
        $this->gEntityManager = $entityManager;
    }

    /**
     * @param Projet $projet
     * Cette fonction permet d'initialiser les états des tests d'un projet après sa création
     */
    public function initEtats(Projet $projet) {
        $etatATester = new EtatTest();
        $etatATester->setProjet($projet);
        $etatATester->setLibelle('A TESTER');
        $etatATester->setIsATester(true);
        $etatATester->setIsOK(false);
        $etatATester->setIsKO(false);

        $this->gEntityManager->persist($etatATester);

        $etatOK = new EtatTest();
        $etatOK->setProjet($projet);
        $etatOK->setLibelle('OK');
        $etatOK->setIsATester(false);
        $etatOK->setIsOK(true);
        $etatOK->setIsKO(false);

        $this->gEntityManager->persist($etatOK);

        $etatKO = new EtatTest();
        $etatKO->setProjet($projet);
        $etatKO->setLibelle('KO');
        $etatKO->setIsATester(false);
        $etatKO->setIsOK(false);
        $etatKO->setIsKO(true);

        $this->gEntityManager->persist($etatKO);

        $this->gEntityManager->flush();
    }


    /**
     * @param Projet $projet
     * @return EtatTest
     * Cette fonction permet de récupérer l'état à tester d'un projet
     */
    public function getEtatATester(Projet $projet) : EtatTest {
        return $this->gEtatTestRepository->findOneBy(['projet' => $projet, 'isATester' => true]);
    }

    /**
     * @param Projet $projet
     * @return EtatTest
     * Cette fonction permet de récupérer l'état OK d'un projet
     */
    public function getEtatOK(Projet $projet) : EtatTest {
        return $this->gEtatTestRepository->findOneBy(['projet' => $projet, 'isOK' => true]);
    }

    /**
     * @param Projet $projet
     * @return EtatTest
     * Cette fonction permet de récupérer l'état KO d'un projet
     */
    public function getEtatKO(Projet $projet) : EtatTest {
        return $this->gEtatTestRepository->findOneBy(['projet' => $projet, 'isKO' => true]);
    }

    /**
     * @param string $libelle
     * @param Projet $projet
     * Cette fonction permet de donner un nouveau libellé à l'état à tester d'un projet
     */
    public function setEtatATester(string $libelle, Projet $projet) {
        $etat = $this->gEtatTestRepository->findOneBy(['projet' => $projet, 'isATester' => true]);
        $etat->setLibelle($libelle);

        $this->gEntityManager->persist($etat);

        $this->gEntityManager->flush();
    }

    /**
     * @param string $libelle
     * @param Projet $projet
     * Cette fonction permet de donner un nouveau libellé à l'état OK d'un projet
     */
    public function setEtatOK(string $libelle, Projet $projet)  {
        $etat =  $this->gEtatTestRepository->findOneBy(['projet' => $projet, 'isOK' => true]);
        $etat->setLibelle($libelle);

        $this->gEntityManager->persist($etat);

        $this->gEntityManager->flush();
    }

    /**
     * @param string $libelle
     * @param Projet $projet
     * Cette fonction permet de donner un nouveau libellé à l'état KO d'un projet
     */
    public function setEtatKO(string $libelle, Projet $projet)  {
        $etat =  $this->gEtatTestRepository->findOneBy(['projet' => $projet, 'isKO' => true]);
        $etat->setLibelle($libelle);

        $this->gEntityManager->persist($etat);

        $this->gEntityManager->flush();
    }

}