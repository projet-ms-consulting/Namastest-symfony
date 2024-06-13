<?php

namespace App\Repository;

use App\Entity\ParticipantProjet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ParticipantProjet|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParticipantProjet|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParticipantProjet[]    findAll()
 * @method ParticipantProjet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParticipantProjet::class);
    }

    // /**
    //  * @return ParticipantProjet[] Returns an array of ParticipantProjet objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ParticipantProjet
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
