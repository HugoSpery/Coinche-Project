<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\ManyToOne]
    private ?User $userSender = null;

    #[ORM\ManyToOne(inversedBy: 'chat')]
    private ?Lobby $lobby = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isNotification = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
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

    public function getLobby(): ?Lobby
    {
        return $this->lobby;
    }

    public function setLobby(?Lobby $lobby): static
    {
        $this->lobby = $lobby;

        return $this;
    }

    public function isNotification(): ?bool
    {
        return $this->isNotification;
    }

    public function setNotification(?bool $isNotification): static
    {
        $this->isNotification = $isNotification;

        return $this;
    }
}
