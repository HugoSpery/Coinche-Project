<?php

namespace App\Service\Request;

use App\Entity\FriendRequest;
use App\Entity\Lobby;
use Doctrine\ORM\EntityManagerInterface;

class RequestManager
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(FriendRequest $request): void
    {
        $this->entityManager->persist($request);
        $this->entityManager->flush();
    }

    public function delete(FriendRequest $request): void
    {
        $this->entityManager->remove($request);
        $this->entityManager->flush();
    }
}