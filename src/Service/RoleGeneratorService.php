<?php

namespace App\Service;

use App\Entity\Projet;
use App\Entity\Role;
use App\Repository\DroitRepository;
use Doctrine\ORM\EntityManagerInterface;

class RoleGeneratorService
{
    private DroitRepository $droitRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        DroitRepository $droitRepository,
        EntityManagerInterface $entityManager) {
        
        $this->droitRepository = $droitRepository;
        $this->entityManager = $entityManager;
    }

    public function generate(Projet $projet): ?Projet {
        if(!$projet) return null;

        $droits = $this->droitRepository->findAll();

        $roles = [
            'invite' => (new Role())->setLibelle('Invité'),
            'administrateur' => (new Role())->setLibelle('Administrateur'),
            'redacteur' => (new Role())->setLibelle('Rédacteur'),
            'testeur' => (new Role())->setLibelle('Testeur'),
            'valideur' => (new Role())->setLibelle('Valideur'),
        ];

        foreach($roles as $libelle => $role) {
            foreach($droits as $d) {
                if($libelle === 'administrateur') {
                    $role->addDroit($d);
                } else if($libelle === 'redacteur' && (
                        $d->getCode() === 'VOIRTEMP' ||
                        $d->getCode() === 'CREETEMP' ||
                        $d->getCode() === 'EDITTEMP' ||
                        $d->getCode() === 'SUPPTEMP' ||
                        $d->getCode() === 'VOIRCATA' ||
                        $d->getCode() === 'CREECATA' ||
                        $d->getCode() === 'EDITCATA' ||
                        $d->getCode() === 'SUPPCATA' ||
                        $d->getCode() === 'VOIRCAMP'
                    )) {

                    $role->addDroit($d);
                } else if($libelle === 'testeur' && (
                        $d->getCode() === 'VOIRCAMP' ||
                        $d->getCode() === 'ETATTEST'
                    )) {

                    $role->addDroit($d);
                } else if($libelle === 'valideur' && (
                        $d->getCode() === 'VOIRCAMP' ||
                        $d->getCode() === 'ETATCAMP' ||
                        $d->getCode() === 'ETATTEST'
                    )) {

                    $role->addDroit($d);
                }
            }

            $this->entityManager->persist($role);

            $projet->addRole($role);
        }

        return $projet;
    }
}