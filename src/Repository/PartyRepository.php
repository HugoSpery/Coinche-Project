<?php

namespace App\Repository;

use App\Entity\Lobby;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lobby>
 */
class PartyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lobby::class);
    }

    //    /**
    //     * @return Lobby[] Returns an array of Lobby objects
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

    //    public function findOneBySomeField($value): ?Lobby
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findOneNotFull(): ?array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('SIZE(p.players) < 4')
            ->andWhere('p.isPublic = true')
            ->andWhere('p.isRanked = false')
            ->getQuery()
            ->getResult();
    }

    public function findOneNotFullRanked(): ?array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('SIZE(p.players) < 4')
            ->andWhere('p.isRanked = true')
            ->getQuery()
            ->getResult();
    }

    public function findOneForTeam() : ?array
    {
        /** @var Lobby[] $lobbies */
        $lobbies = $this->createQueryBuilder('p')
            ->andWhere('SIZE(p.players) < 4')
            ->andWhere('p.isPublic = true')
            ->andWhere('p.isRanked = true')
            ->getQuery()
            ->getResult();


        foreach ($lobbies as $l) {
            if ($l->getTeamBlue()->count() == 0){
                return [$l,"blue"];
            }
            if ($l->getTeamRed()->count() == 0){
                return [$l,"red"];
            }
        }

        return null;
    }
}
