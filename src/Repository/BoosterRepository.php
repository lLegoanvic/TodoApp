<?php

namespace App\Repository;

use App\Entity\Booster;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booster>
 *
 * @method Booster|null find($id, $lockMode = null, $lockVersion = null)
 * @method Booster|null findOneBy(array $criteria, array $orderBy = null)
 * @method Booster[]    findAll()
 * @method Booster[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoosterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booster::class);
    }

//    /**
//     * @return Booster[] Returns an array of Booster objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Booster
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
