<?php

namespace App\Repository;

use App\Entity\TemplateTest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TemplateTest|null find($id, $lockMode = null, $lockVersion = null)
 * @method TemplateTest|null findOneBy(array $criteria, array $orderBy = null)
 * @method TemplateTest[]    findAll()
 * @method TemplateTest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TemplateTestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TemplateTest::class);
    }


    /**
     * @param $idProjet
     * @return int|mixed|string|null
     * @throws NonUniqueResultException
     *
     * cette fonction permet de créer la position d'un cas de test dans la liste des cas de tests d'un projet
     */
    public function createPositionOfTest($idProjet){
        return $this->createQueryBuilder('t')
            ->addSelect('MAX(t.position) as p')
            ->andWhere('t.projet = :idProjet')
            ->setParameter('idProjet', $idProjet)
            ->groupBy('t.id')
            ->orderBy('p', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function findTemplateByProject($idProjet){
        return $this->createQueryBuilder('t')
            ->addSelect('t')
            ->andWhere('t.projet = :idProjet')
            ->setParameter('idProjet', $idProjet)
            ->orderBy('t.position', 'ASC')
            ->getQuery()
            //https://stackoverflow.com/questions/14282685/symfony2-how-to-get-foreign-key-from-database
            ->setHint(\Doctrine\ORM\Query::HINT_INCLUDE_META_COLUMNS, true)
            ->getArrayResult();

    }

    public function findMaxCopies($test){
        return $this->createQueryBuilder('t')
            ->select('COUNT(t)')
            ->andWhere('t.nom LIKE :test')
            ->setParameter('test', $test.' Copie%')
            ->getQuery()
            ->getSingleScalarResult();

    }


    // /**
    //  * @return TemplateTest[] Returns an array of TemplateTest objects
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
    public function findOneBySomeField($value): ?TemplateTest
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
