<?php

namespace App\Repository;

use App\Entity\TemplateCatalogueRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TemplateCatalogueRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TemplateCatalogueRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TemplateCatalogueRelation[]    findAll()
 * @method TemplateCatalogueRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TemplateCatalogueRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TemplateCatalogueRelation::class);
    }



    /**
     * @param $idCatalogue
     * @return int|mixed|string|null
     * @throws NonUniqueResultException
     *
     * cette fonction permet de créer la position d'un test dans un catalogue
     */
    public function createPositionOfTest($idCatalogue) {
        return $this->createQueryBuilder('t')
            ->addSelect('MAX(t.ordre) as p')
            ->andWhere('t.catalogue = :cat')
            ->setParameter('cat', $idCatalogue)
            ->groupBy('t.id')
            ->orderBy('p', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    // /**
    //  * @return TemplateCatalogueRelation[] Returns an array of TemplateCatalogueRelation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TemplateCatalogueRelation
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
