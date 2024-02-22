<?php

namespace App\Repository;

use App\Entity\TypePointOfInterest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypePointOfInterest>
 *
 * @method TypePointOfInterest|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypePointOfInterest|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypePointOfInterest[]    findAll()
 * @method TypePointOfInterest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypePointOfInterestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypePointOfInterest::class);
    }

//    /**
//     * @return TypePointOfInterest[] Returns an array of TypePointOfInterest objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TypePointOfInterest
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
