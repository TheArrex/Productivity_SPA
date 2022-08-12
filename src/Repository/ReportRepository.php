<?php

namespace App\Repository;

use App\Entity\Report;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Report|null find($id, $lockMode = null, $lockVersion = null)
 * @method Report|null findOneBy(array $criteria, array $orderBy = null)
 * @method Report[]    findAll()
 * @method Report[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    // /**
    //  * @return Report[] Returns an array of Report objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function findAverageRateForWeek($teamId, $week)
    {
        return $this->createQueryBuilder('r')
            ->select('round(avg(r.rate), 1)')
            ->join('r.user', 'u')
            ->where('u.team_id = :team_id')
            ->andWhere('r.week = :week')
            ->andWhere('r.is_published = 1')
            ->setParameter('team_id', $teamId)
            ->setParameter('week', $week)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param $userId
     * @param $week
     * @return int|array|string
     */
    public function findUserRateForWeek($userId, $week)
    {
        return $this->createQueryBuilder('r')
            ->select('r.rate')
            ->where('r.user = :user_id')
            ->andWhere('r.week = :week')
            ->andWhere('r.is_published = 1')
            ->setParameter('user_id', $userId)
            ->setParameter('week', $week)
            ->getQuery()
            ->getScalarResult();
    }
}
