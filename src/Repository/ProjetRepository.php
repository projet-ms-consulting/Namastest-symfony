<?php

namespace App\Repository;

use App\Entity\Projet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Projet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Projet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Projet[]    findAll()
 * @method Projet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projet::class);
    }

    public function findOneById($id): ?Projet {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.participantsProjet', 'pp')
            ->leftJoin('pp.role', 'r')
            ->leftJoin('r.droits', 'd')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAllByAuteur($user) {
        return $this->createQueryBuilder('p')
            ->andWhere('p.auteur = :user')
            ->setParameter('user', $user)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findAllByParticipant($user) {
        // J'ai modifié cette requête car elle ne renvoyait pas les projet mais l'entité projetParticipant à la place
//        return $this->createQueryBuilder('p')
//            ->select('participants')
//            ->from('App:ParticipantProjet', 'participants')
//            ->andWhere('participants.utilisateur = :user')
//            ->setParameter('user', $user)
//            ->orderBy('p.id', 'ASC')
//            ->getQuery()
//            ->getResult()
//        ;

        return $this->createQueryBuilder('pr')
            ->leftJoin('pr.participantsProjet', 'pa')
            ->where('pa.utilisateur = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }
}
