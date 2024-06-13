<?php

namespace App\Repository;

use App\Entity\EtatTest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EtatTest|null find($id, $lockMode = null, $lockVersion = null)
 * @method EtatTest|null findOneBy(array $criteria, array $orderBy = null)
 * @method EtatTest[]    findAll()
 * @method EtatTest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtatTestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EtatTest::class);
    }

    // /**
    //  * @return EtatTest[] Returns an array of EtatTest objects
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
    public function findOneBySomeField($value): ?EtatTest
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
