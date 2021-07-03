<?php

namespace App\Repository;

use App\Entity\Musique;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Musique|null find($id, $lockMode = null, $lockVersion = null)
 * @method Musique|null findOneBy(array $criteria, array $orderBy = null)
 * @method Musique[]    findAll()
 * @method Musique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MusiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Musique::class);
    }

    /**
     * Return les musiques par date de création récente
     *
     * @param integer $nb
     * @return array
     */
    public function findRecent(int $nb = null): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.id', 'DESC')
            ->setMaxResults($nb)
            ->getQuery()
            ->getResult();
    }

    public function findByUserInInterval($user, $d1, $d2)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.createdAt BETWEEN :d1 AND :d2')
            ->andWhere('m.user = :user')
            ->setParameter('user', $user)
            ->setParameter('d1', $d1)
            ->setParameter('d2', $d2)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Musique[] Returns an array of Musique objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Musique
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
