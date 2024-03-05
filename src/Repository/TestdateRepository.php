<?php

namespace App\Repository;

use App\Entity\Testdate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Testdate>
 *
 * @method Testdate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Testdate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Testdate[]    findAll()
 * @method Testdate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestdateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Testdate::class);
    }

    //    /**
    //     * @return Testdate[] Returns an array of Testdate objects
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

    //    public function findOneBySomeField($value): ?Testdate
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
