<?php

namespace App\Repository;

use App\Entity\Catalogue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Catalogue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Catalogue|null findOneBy(array $criteria, array $orderBy = null)
 * @method Catalogue[]    findAll()
 * @method Catalogue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CatalogueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Catalogue::class);
    }

    /**
     * @return Catalogue[] Returns an array of Catalogue objects
     */
    public function findCatalogueWithJoinsOrdered($projetId, $catalogueId)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.templateCatalogueRelation', 'r')
            ->addSelect('r')
            ->leftJoin('r.template', 't')
            ->addSelect('t')
            ->andWhere('c.projet = :projet')
            ->setParameter('projet', $projetId)
            ->andWhere('c = :cat')
            ->setParameter('cat', $catalogueId)
            ->orderBy('r.ordre', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?Catalogue
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
