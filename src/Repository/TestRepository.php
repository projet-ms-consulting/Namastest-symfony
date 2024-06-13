<?php

namespace App\Repository;

use App\Entity\Test;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Test|null find($id, $lockMode = null, $lockVersion = null)
 * @method Test|null findOneBy(array $criteria, array $orderBy = null)
 * @method Test[]    findAll()
 * @method Test[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Test::class);
    }

    /**
     * @param $idCampagne
     * @return int|mixed|string|null
     * @throws NonUniqueResultException
     *
     * cette fonction permet de créer la position d'un test dans une campagne
     */
    public function createPositionOfTest($idCampagne){
        return $this->createQueryBuilder('t')
            ->addSelect('MAX(t.ordre) as p')
            ->andWhere('t.campagne = :idCampagne')
            ->setParameter('idCampagne', $idCampagne)
            ->groupBy('t.id')
            ->orderBy('p', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @param $campagne
     * @return int|mixed|string
     *
     * Fonction pour chercher la liste des tests dans une campagne, accompagné de son template, son état
     * ainsi que ses catalogues. Tout ceci, ordonné par le champ ordre
     */
    public function findByCampagneWithJoins($campagne)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.campagne = :cpgn')
            ->setParameter('cpgn', $campagne)
            ->leftJoin('t.template', 'tm')
            ->addSelect('tm')
            ->leftJoin('tm.templateCatalogueRelation', 'r')
            ->addSelect('r')
            ->leftJoin('r.catalogue', 'cat')
            ->addSelect('cat')
            ->leftJoin('t.etat', 'etat')
            ->addSelect('etat')
            ->orderBy('t.ordre', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Test
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
