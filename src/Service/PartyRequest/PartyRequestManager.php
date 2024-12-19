<?php

namespace App\Service\PartyRequest;

use App\Entity\FriendRequest;
use App\Entity\PartyRequest;
use Doctrine\ORM\EntityManagerInterface;

class PartyRequestManager
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(PartyRequest $request): void
    {
        $this->entityManager->persist($request);
        $this->entityManager->flush();
    }

    public function delete(PartyRequest $request): void
    {
        $this->entityManager->remove($request);
        $this->entityManager->flush();
    }
}