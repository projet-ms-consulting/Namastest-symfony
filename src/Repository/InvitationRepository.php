<?php

namespace App\Repository;

use App\Entity\Invitation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Invitation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invitation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invitation[]    findAll()
 * @method Invitation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invitation::class);
    }

    /*
     * PERMET DE VERIFIER LA CONTRAINTE "UniqueEntity" DES INVITATIONS (UNE SEULE INVITATION EN ATTENTE A LA FOIS)
     */
    public function uniqueEntityCheck($values) {
        return $this->createQueryBuilder('i')
            ->andWhere('i.projet = :projet')
            ->setParameter('projet', $values['projet'])
            ->andWhere('i.destinataire = :destinataire')
            ->setParameter('destinataire', $values['destinataire'])
            ->andWhere('i.etat = :etat')
            ->setParameter('etat', Invitation::EN_ATTENTE)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Invitation[] Returns an array of Invitation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Invitation
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
