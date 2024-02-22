<?php

namespace App\Repository;

use App\Entity\Essential;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Essential>
 *
 * @method Essential|null find($id, $lockMode = null, $lockVersion = null)
 * @method Essential|null findOneBy(array $criteria, array $orderBy = null)
 * @method Essential[]    findAll()
 * @method Essential[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EssentialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Essential::class);
    }

//    /**
//     * @return Essential[] Returns an array of Essential objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Essential
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
