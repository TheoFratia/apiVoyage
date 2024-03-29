<?php

namespace App\Repository;

use App\Entity\TypeInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeInfo>
 *
 * @method TypeInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeInfo[]    findAll()
 * @method TypeInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeInfo::class);
    }

//    /**
//     * @return TypeInfo[] Returns an array of TypeInfo objects
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

//    public function findOneBySomeField($value): ?TypeInfo
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
