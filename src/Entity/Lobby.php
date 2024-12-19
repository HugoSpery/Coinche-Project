<?php

namespace App\Entity;

use App\Repository\PartyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;

#[ORM\Entity(repositoryClass: PartyRepository::class)]
class Lobby
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $code = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'party')]
    private Collection $players;

    #[ORM\Column(nullable: true)]
    private ?bool $isPublic = null;

    #[ORM\OneToOne(inversedBy: 'lobby', cascade: ['persist', 'remove'])]
    private ?User $chief = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isRanked = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[JoinTable(name: 'lobby_team_blue')]
    private Collection $teamBlue;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[JoinTable(name: 'lobby_team_red')]
    private Collection $teamRed;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'lobby')]
    private Collection $chat;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->teamBlue = new ArrayCollection();
        $this->teamRed = new ArrayCollection();
        $this->code = bin2hex(random_bytes(4));
        $this->chat = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(User $player): static
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
            $player->setParty($this);
        }

        return $this;
    }

    public function removePlayer(User $player): static
    {
        if ($this->players->removeElement($player)) {
            $this->teamRed->removeElement($player);
            $this->teamBlue->removeElement($player);
            $player->setParty(null);
            $player->setReady(false);
            if ($player->getId() === $this->getChief()->getId()) {
                if ($this->players->count() > 0) {
                    $this->setChief($this->players->first());
                } else {
                    $this->setChief(null);
                }
            }


        }

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setPublic(?bool $isPublic): static
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    public function getChief(): ?User
    {
        return $this->chief;
    }

    public function setChief(?User $chief): static
    {
        $this->chief = $chief;

        return $this;
    }

    public function isRanked(): ?bool
    {
        return $this->isRanked;
    }

    public function setRanked(?bool $isRanked): static
    {
        $this->isRanked = $isRanked;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getTeamBlue(): Collection
    {
        return $this->teamBlue;
    }

    public function addTeamBlue(User $teamBlue): static
    {
        if (!$this->teamBlue->contains($teamBlue)) {
            $this->teamBlue->add($teamBlue);
        }

        return $this;
    }

    public function removeTeamBlue(User $teamBlue): static
    {
        $this->teamBlue->removeElement($teamBlue);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getTeamRed(): Collection
    {
        return $this->teamRed;
    }

    public function addTeamRed(User $teamRed): static
    {
        if (!$this->teamRed->contains($teamRed)) {
            $this->teamRed->add($teamRed);
        }

        return $this;
    }

    public function removeTeamRed(User $teamRed): static
    {
        $this->teamRed->removeElement($teamRed);

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getChat(): Collection
    {
        return $this->chat;
    }

    public function orderChatById(){
        $iterator = $this->chat->getIterator();
        $iterator->uasort(function ($a, $b) {
            return ($a->getId() < $b->getId()) ? -1 : 1;
        });
        $this->chat = new ArrayCollection(iterator_to_array($iterator));
    }

    public function addChat(Message $chat): static
    {
        if (!$this->chat->contains($chat)) {
            $this->chat->add($chat);
            $chat->setLobby($this);
        }

        return $this;
    }

    public function removeChat(Message $chat): static
    {
        if ($this->chat->removeElement($chat)) {
            // set the owning side to null (unless already changed)
            if ($chat->getLobby() === $this) {
                $chat->setLobby(null);
            }
        }

        return $this;
    }
}
