<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    //    /**
    //     * @return Game[] Returns an array of Game objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Game
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getNbGamePlayed(User $user): int
    {
        return $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->andWhere(':user MEMBER OF g.players')
            ->setParameter('user', $user)
            ->andWhere('g.isEnd = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getWinGamePlayed(User $user) : int{
        return $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->where(':user MEMBER OF g.teamBlue AND g.pointsBlue > g.pointsRed')
            ->orWhere(':user MEMBER OF g.teamRed AND g.pointsRed > g.pointsBlue')
            ->andWhere('g.isEnd = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function trophyEvolution(User $user){
        $tab = $this->createQueryBuilder('g')
            ->where(':user MEMBER OF g.players')
            ->andWhere('g.isEnd = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $result = [];
        $nb = 0;
        $result[] = $nb;
        foreach ($tab as $elt){
            if ($elt->getPointsBlue() > $elt->getPointsRed()){
                if ($elt->getPointsBlue() - $elt->getPointsRed() >= 600){
                    $points = 50;
                }else if($elt->getPointsBlue() - $elt->getPointsRed() >= 300) {
                    $points = 25;
                }
                else{
                    $points = 15;
                }
            }else{
                if ($elt->getPointsRed() - $elt->getPointsBlue() >= 600){
                    $points = 50;
                }else if($elt->getPointsRed() - $elt->getPointsBlue() >= 300) {
                    $points = 25;
                }
                else{
                    $points = 15;
                }
            }

            if ($elt->getTeamBlue()->contains($user) && $elt->getPointsBlue() > $elt->getPointsRed()) {
                $nb += $points;
            }
            else if($elt->getTeamRed()->contains($user) && $elt->getPointsRed() > $elt->getPointsBlue()){
                $nb += $points;
            }
            else{
                $nb -= $points;
            }

            if($nb < 0){
                $nb = 0;
            }
            $result[] = $nb;
        }

        return $result;
    }


    public function gameWonEvolution(User $user) : array{
        $tab = $this->createQueryBuilder('g')
            ->where(':user MEMBER OF g.players')
            ->andWhere('g.isEnd = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $nb = 0;
        $result = [];
        $result[] = $nb;
        foreach ($tab as $elt){
            if ($elt->getPointsBlue() > $elt->getPointsRed() && $elt->getTeamBlue()->contains($user)){
                $nb++;
            }
            else if ($elt->getPointsRed() > $elt->getPointsBlue() && $elt->getTeamRed()->contains($user)){
                $nb++;
            }else{
                $nb--;
            }

            $result[] = $nb;
        }
        return $result;

    }
}
