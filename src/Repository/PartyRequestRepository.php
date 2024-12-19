<?php

namespace App\Repository;

use App\Entity\PartyRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PartyRequest>
 */
class PartyRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PartyRequest::class);
    }

    //    /**
    //     * @return PartyRequest[] Returns an array of PartyRequest objects
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

    //    public function findOneBySomeField($value): ?PartyRequest
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getUsernameReceiverRequest($userSender){
        $result = $this->createQueryBuilder('p')
            ->select('u.username')
            ->join('p.userReceiver','u')
            ->where('p.userSender = :user_sender')
            ->setParameter('user_sender',$userSender)
            ->getQuery()
            ->getResult();

        $realResult = [];
        foreach ($result as $key => $value){
            $realResult[] = $value['username'];
        }
        return $realResult;
    }

    public function getUsernameSenderRequest($userReceiver){
        $result = $this->createQueryBuilder('p')
            ->select('u.username')
            ->join('p.userSender','u')
            ->where('p.userReceiver = :user_receiver')
            ->setParameter('user_receiver',$userReceiver)
            ->getQuery()
            ->getResult();

        $realResult = [];
        foreach ($result as $key => $value){
            $realResult[] = $value['username'];
        }
        return $realResult;
    }



    public function deleteExpiredRequest()
    {
        $query = $this->createQueryBuilder('p')
            ->delete()
            ->where('p.date < :date')
            ->setParameter('date', new \DateTime('now -10 minutes'))
            ->getQuery();
        $query->execute();
    }
}
