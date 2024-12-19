<?php

namespace App\Service\Game;

use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;

class GameManager
{
    public function __construct(private EntityManagerInterface $em){

    }

    public function save(Game $game){
        $this->em->persist($game);
        $this->em->flush();
    }

    public function delete(Game $game){
        $this->em->remove($game);
        $this->em->flush();
    }
}