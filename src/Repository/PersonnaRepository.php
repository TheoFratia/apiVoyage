<?php

namespace App\Repository;

use App\Entity\Personna;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Personna>
 *
 * @method Personna|null find($id, $lockMode = null, $lockVersion = null)
 * @method Personna|null findOneBy(array $criteria, array $orderBy = null)
 * @method Personna[]    findAll()
 * @method Personna[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonnaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personna::class);
    }

//    /**
//     * @return Personna[] Returns an array of Personna objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Personna
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
