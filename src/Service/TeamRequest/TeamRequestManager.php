<?php

namespace App\Service\TeamRequest;

use App\Entity\FriendRequest;
use App\Entity\Lobby;
use App\Entity\TeamRequest;
use App\Repository\TeamRequestRepository;
use Doctrine\ORM\EntityManagerInterface;

class TeamRequestManager
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(TeamRequest $request): void
    {
        $this->entityManager->persist($request);
        $this->entityManager->flush();
    }

    public function delete(TeamRequest $request): void
    {
        $this->entityManager->remove($request);
        $this->entityManager->flush();
    }
}