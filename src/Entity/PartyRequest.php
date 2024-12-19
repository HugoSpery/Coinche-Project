<?php

namespace App\Entity;

use App\Repository\PartyRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartyRequestRepository::class)]
class PartyRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?User $userSender = null;

    #[ORM\ManyToOne]
    private ?User $userReceiver = null;


    #[ORM\Column]
    private ?string $codeGame = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserSender(): ?User
    {
        return $this->userSender;
    }

    public function setUserSender(?User $userSender): static
    {
        $this->userSender = $userSender;

        return $this;
    }

    public function getUserReceiver(): ?User
    {
        return $this->userReceiver;
    }

    public function setUserReceiver(?User $userReceiver): static
    {
        $this->userReceiver = $userReceiver;

        return $this;
    }
    
    public function getCodeGame(): ?string
    {
        return $this->codeGame;
    }

    public function setCodeGame(string $codeGame): static
    {
        $this->codeGame = $codeGame;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }
}