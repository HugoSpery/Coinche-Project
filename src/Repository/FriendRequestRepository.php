<?php

namespace App\Repository;

use App\Entity\FriendRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FriendRequest>
 */
class FriendRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry,private UserRepository $userRepository)
    {
        parent::__construct($registry, FriendRequest::class);
    }

    //    /**
    //     * @return FriendRequest[] Returns an array of FriendRequest objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FriendRequest
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getUsernameReceiverRequest($userSender){
         $result = $this->createQueryBuilder('f')
            ->select('u.username')
            ->join('f.userReceiver','u')
            ->where('f.userSender = :userSender')
            ->setParameter('userSender',$userSender)
            ->getQuery()
            ->getResult();

         $realResult = [];
         foreach ($result as $key => $value){
             $realResult[] = $value['username'];
         }
         return $realResult;
    }

    public function getUsernameSenderRequest($userReceiver,$query){
         $result = $this->createQueryBuilder('f')
            ->select('u.username')
            ->join('f.userSender','u')
            ->where('f.userReceiver = :userReceiver')
            ->setParameter('userReceiver',$userReceiver)
            ->getQuery()
            ->getResult();

        $realResult = [];
        foreach ($result as $key => $value) {
            if (str_contains(strtolower($value['username']),strtolower($query))){
                $realResult[] = $this->userRepository->findOneBy(["username"=>$value['username']]);
            }
        }
        return $realResult;

    }

}
