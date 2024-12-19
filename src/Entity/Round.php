<?php

namespace App\Entity;

use App\Enum\Type;
use App\Repository\RoundRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoundRepository::class)]
class Round
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?Type $type = null;

    #[ORM\Column(length: 255,nullable: true)]
    private ?string $colorTeam = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_win = null;

    #[ORM\ManyToOne(inversedBy: 'rounds')]
    private ?Game $game = null;

    #[ORM\Column(nullable: true)]
    private ?int $cpt = null;

    #[ORM\ManyToOne]
    private ?User $player = null;

    #[ORM\ManyToOne(cascade: ['persist'])]
    private ?User $waitingUser = null;

    #[ORM\Column(nullable: true)]
    private ?int $points = null;

    #[ORM\Column(nullable: true)]
    private ?int $pointsRed = null;

    #[ORM\Column(nullable: true)]
    private ?int $pointsBlue = null;

    #[ORM\ManyToOne]
    private ?User $startPlayer = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isStart = null;

    /**
     * @var Collection<int, Card>
     */
    #[ORM\ManyToMany(targetEntity: Card::class)]
    private Collection $heap;

    #[ORM\Column(nullable: true)]
    private ?int $announceBlue = null;

    #[ORM\Column(nullable: true)]
    private ?int $announceRed = null;

    #[ORM\ManyToOne]
    private ?User $playerAnnounce = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY,nullable: true)]
    private ?array $announceName = [];

    


    public function __construct()
    {
        $this->heap = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): Round
    {
        $this->type = $type;
        return $this;
    }



    public function getColorTeam(): ?string
    {
        return $this->colorTeam;
    }

    public function setColorTeam(string $colorTeam): static
    {
        $this->colorTeam = $colorTeam;

        return $this;
    }

    public function isWin(): ?bool
    {
        return $this->is_win;
    }

    public function setWin(?bool $is_win): static
    {
        $this->is_win = $is_win;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;

        return $this;
    }

    public function getCpt(): ?int
    {
        return $this->cpt;
    }

    public function setCpt(int $cpt): static
    {
        $this->cpt = $cpt;

        return $this;
    }

    public function getPlayer(): ?User
    {
        return $this->player;
    }

    public function setPlayer(?User $player): static
    {
        $this->player = $player;

        return $this;
    }

    public function getWaitingUser(): ?User
    {
        return $this->waitingUser;
    }

    public function setWaitingUser(?User $waitingUser): static
    {
        $this->waitingUser = $waitingUser;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function getPointsRed(): ?int
    {
        return $this->pointsRed;
    }

    public function setPointsRed(?int $pointsRed): static
    {
        $this->pointsRed = $pointsRed;

        return $this;
    }

    public function getPointsBlue(): ?int
    {
        return $this->pointsBlue;
    }

    public function setPointsBlue(?int $pointsBlue): static
    {
        $this->pointsBlue = $pointsBlue;

        return $this;
    }

    public function getStartPlayer(): ?User
    {
        return $this->startPlayer;
    }

    public function setStartPlayer(?User $startPlayer): static
    {
        $this->startPlayer = $startPlayer;

        return $this;
    }

    public function isStart(): ?bool
    {
        return $this->isStart;
    }

    public function setStart(?bool $isStart): static
    {
        $this->isStart = $isStart;

        return $this;
    }

    /**
     * @return Collection<int, Card>
     */
    public function getHeap(): Collection
    {
        return $this->heap;
    }

    public function addHeap(Card $heap): static
    {
        if (!$this->heap->contains($heap)) {
            $this->heap->add($heap);
        }

        return $this;
    }

    public function removeHeap(Card $heap): static
    {
        $this->heap->removeElement($heap);

        return $this;
    }

    public function setHeap(Collection $heap)
    {
        $this->heap = $heap;
    }

    public function getAnnounceBlue(): ?int
    {
        return $this->announceBlue;
    }

    public function setAnnounceBlue(?int $announceBlue): static
    {
        $this->announceBlue = $announceBlue;

        return $this;
    }

    public function getAnnounceRed(): ?int
    {
        return $this->announceRed;
    }

    public function setAnnounceRed(?int $announceRed): static
    {
        $this->announceRed = $announceRed;

        return $this;
    }

    public function getPlayerAnnounce(): ?User
    {
        return $this->playerAnnounce;
    }

    public function setPlayerAnnounce(?User $playerAnnounce): static
    {
        $this->playerAnnounce = $playerAnnounce;

        return $this;
    }

    public function getAnnounceName(): ?array
    {
        return $this->announceName;
    }

    public function setAnnounceName(?array $announceName): static
    {
        $this->announceName = $announceName;

        return $this;
    }

    public function addAnnounceName(string $announceName): static
    {
        $this->announceName[] = $announceName;
        return $this;
    }


}
