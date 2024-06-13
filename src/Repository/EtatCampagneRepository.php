<?php

namespace App\Repository;

use App\Entity\EtatCampagne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EtatCampagne|null find($id, $lockMode = null, $lockVersion = null)
 * @method EtatCampagne|null findOneBy(array $criteria, array $orderBy = null)
 * @method EtatCampagne[]    findAll()
 * @method EtatCampagne[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtatCampagneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EtatCampagne::class);
    }

    // /**
    //  * @return EtatCampagne[] Returns an array of EtatCampagne objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EtatCampagne
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
