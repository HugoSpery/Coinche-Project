<?php

namespace App\Service\Party;

use App\Entity\Lobby;
use Doctrine\ORM\EntityManagerInterface;

class PartyManager
{

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(Lobby $party): void
    {
        $this->entityManager->persist($party);
        $this->entityManager->flush();
    }

    public function delete(Lobby $party): void
    {
        $this->entityManager->remove($party);
        $this->entityManager->flush();
    }

}