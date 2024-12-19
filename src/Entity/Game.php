<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $code = null;

    private ?CardPack $cardPack = null;

    #[ORM\Column(nullable: true)]
    private ?int $pointsRed = null;

    #[ORM\Column(nullable: true)]
    private ?int $pointsBlue = null;

    #[ORM\Column(nullable: true)]
    private ?bool $turnBlue = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[JoinTable(name: 'game_team_red')]

    private Collection $teamRed;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class,)]
    #[JoinTable(name: 'game_team_blue')]
    private Collection $teamBlue;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    private Collection $players;

    /**
     * @var Collection<int, Round>
     */
    #[ORM\OneToMany(targetEntity: Round::class, mappedBy: 'game',cascade: ['persist'])]
    private Collection $rounds;

    #[ORM\Column(nullable: true)]
    private ?bool $isEnd = null;


    public function __construct()
    {
        $this->teamRed = new ArrayCollection();
        $this->teamBlue = new ArrayCollection();
        $this->players = new ArrayCollection();
        $this->rounds = new ArrayCollection();
    }




    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Game
    {
        $this->id = $id;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

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

    public function isTurnBlue(): ?bool
    {
        return $this->turnBlue;
    }
    public function getIsEnd(): ?bool
    {
        return $this->isEnd;
    }

    public function setIsEnd(?bool $isEnd): Game
    {
        $this->isEnd = $isEnd;
        return $this;
    }

    public function setTurnBlue(?bool $turnBlue): static
    {
        $this->turnBlue = $turnBlue;

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
     * @return Collection<int, User>
     */
    public function getTeamBlue(): Collection
    {
        return $this->teamBlue;
    }

    public function setTeamBlue(Collection $teamBlue): Game
    {
        $this->teamBlue = $teamBlue;
        return $this;
    }

    public function setTeamRed(Collection $teamRed): Game
    {
        $this->teamRed = $teamRed;
        return $this;
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

    public function getCardPack(): ?CardPack
    {
        return $this->cardPack;
    }

    public function setCardPack(?CardPack $cardPack): Game
    {
        $this->cardPack = $cardPack;
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
        }

        return $this;
    }

    public function removePlayer(User $player): static
    {
        $this->players->removeElement($player);

        return $this;
    }

    public function setPlayers(Collection $players): Game
    {
        $this->players = $players;
        return $this;
    }

    public function getOtherTeamPlayer(User $user,string $team)
    {
        if ($team === "red"){
            if ($this->teamRed[0]->getId() === $user->getId()) {
                return $this->teamRed[1];
            }else{
                return $this->teamRed[0];
            }
        }else{
            if ($this->teamBlue[0]->getId() === $user->getId()) {
                return $this->teamBlue[1];
            }else{
                return $this->teamBlue[0];
            }
        }
    }

    public function shufflePlayers()
    {
        // Convertir la collection en tableau
        $players = $this->players->toArray();

        // Mélanger le tableau
        shuffle($players);

        // Recréer la collection à partir du tableau mélangé
        $this->players = new ArrayCollection($players);

    }

    /**
     * @return Collection<int, Round>
     */
    public function getRounds(): Collection
    {
        return $this->rounds;
    }

    public function addRound(Round $round): static
    {
        if (!$this->rounds->contains($round)) {
            $this->rounds->add($round);
            $round->setGame($this);
        }

        return $this;
    }

    public function removeRound(Round $round): static
    {
        if ($this->rounds->removeElement($round)) {
            // set the owning side to null (unless already changed)
            if ($round->getGame() === $this) {
                $round->setGame(null);
            }
        }

        return $this;
    }

    public function getLastRound()
    {
        return $this->rounds->last();
    }

    public function isEnd(): ?bool
    {
        return $this->isEnd;
    }

    public function setEnd(?bool $isEnd): static
    {
        $this->isEnd = $isEnd;

        return $this;
    }


}
