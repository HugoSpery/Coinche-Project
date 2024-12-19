<?php

namespace App\Service\Round;

use App\Entity\Round;
use Doctrine\ORM\EntityManagerInterface;

class RoundManager
{

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(Round $round): void
    {
        $this->entityManager->persist($round);
        $this->entityManager->flush();
    }

    public function delete(Round $round): void
    {
        $this->entityManager->remove($round);
        $this->entityManager->flush();
    }
}