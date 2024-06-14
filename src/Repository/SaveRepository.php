<?php

namespace App\Repository;

use App\Entity\Save;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Save>
 *
 * @method Save|null find($id, $lockMode = null, $lockVersion = null)
 * @method Save|null findOneBy(array $criteria, array $orderBy = null)
 * @method Save[]    findAll()
 * @method Save[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Save::class);
    }

//    /**
//     * @return Save[] Returns an array of Save objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Save
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @return Save[]
     */
    public function findByUserIdAndGeoId($userId, $geoId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.UserId = :userId')
            ->andWhere('s.idGeo = :geoId')
            ->setParameter('userId', $userId)
            ->setParameter('geoId', $geoId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Supprime une entité Save en utilisant idPointOfInterest et idUser.
     *
     * @param int $idPointOfInterest
     * @param int $idUser
     */
    public function deleteByPointOfInterestAndUser(int $idPointOfInterest, int $idUser): void
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'DELETE FROM App\Entity\Save s 
             WHERE :pointOfInterest MEMBER OF s.idPointOfInterest 
             AND s.UserId = :idUser'
        )
        ->setParameter('pointOfInterest', $idPointOfInterest)
        ->setParameter('idUser', $idUser);

        $query->execute();
    }
}
