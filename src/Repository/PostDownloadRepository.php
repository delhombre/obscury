<?php

namespace App\Repository;

use App\Entity\PostDownload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PostDownload|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostDownload|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostDownload[]    findAll()
 * @method PostDownload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostDownloadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostDownload::class);
    }

    // /**
    //  * @return PostDownload[] Returns an array of PostDownload objects
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
    public function findOneBySomeField($value): ?PostDownload
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
