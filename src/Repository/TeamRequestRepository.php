<?php

namespace App\Repository;

use App\Entity\TeamRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamRequest>
 */
class TeamRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamRequest::class);
    }

    //    /**
    //     * @return TeamRequest[] Returns an array of TeamRequest objects
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

    //    public function findOneBySomeField($value): ?TeamRequest
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getUsernameSenderTeamRequest($userReceiver){
        $result = $this->createQueryBuilder('t')
            ->select('u.username')
            ->join('t.userSender','u')
            ->where('t.userReceiver = :user_receiver')
            ->setParameter('user_receiver',$userReceiver)
            ->getQuery()
            ->getResult();

        $realResult = [];
        foreach ($result as $key => $value){
            $realResult[] = $value['username'];
        }
        return $realResult;
    }

    public function getUsernameReceiverTeamRequest($userSender){
        $result = $this->createQueryBuilder('t')
            ->select('u.username')
            ->join('t.userReceiver','u')
            ->where('t.userSender = :user_sender')
            ->setParameter('user_sender',$userSender)
            ->getQuery()
            ->getResult();

        $realResult = [];
        foreach ($result as $key => $value){
            $realResult[] = $value['username'];
        }
        return $realResult;
    }

    public function deleteExpiredTeamRequest(){
        $this->createQueryBuilder('t')
            ->delete()
            ->where('t.date < :date')
            ->setParameter('date',new \DateTime('now -10 minutes'))
            ->getQuery()
            ->execute();

    }
}
