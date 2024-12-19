<?php

namespace App\Entity;

use App\Repository\HandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HandRepository::class)]
class Hand
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Card>
     */
    #[ORM\ManyToMany(targetEntity: Card::class)]
    private Collection $cards;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function orderCard(): void
    {
        $cards = $this->cards;


        $clubCards = [];
        $heartCards = [];
        $diamondCards = [];
        $spadeCards = [];
        foreach ($cards as $card) {
            if ($card->getType()->value === "spades") {
                $spadeCards[] = $card;
            } else if ($card->getType()->value === "hearts") {
                $heartCards[] = $card;
            } else if ($card->getType()->value === "diamonds") {
                $diamondCards[] = $card;
            } else {
                $clubCards[] = $card;
            }
        }
        usort($clubCards, function($a, $b) {
            return $a->getNumber() - $b->getNumber();
        });
        usort($heartCards, function($a, $b) {
            return $a->getNumber() - $b->getNumber();
        });
        usort($diamondCards, function($a, $b) {
            return $a->getNumber() - $b->getNumber();
        });
        usort($spadeCards, function($a, $b) {
            return $a->getNumber() - $b->getNumber();
        });

        $cardsSort= new ArrayCollection();
        foreach ($clubCards as $card) {
            $cardsSort->add($card);
        }
        foreach ($heartCards as $card) {
            $cardsSort->add($card);
        }
        foreach ($spadeCards as $card) {
            $cardsSort->add($card);
        }
        foreach ($diamondCards as $card) {
            $cardsSort->add($card);
        }
        $this->cards = $cardsSort;
    }

    /**
     * @return Collection<int, Card>
     */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): static
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
        }

        return $this;
    }

    public function removeCard(Card $card): static
    {
        $this->cards->removeElement($card);

        return $this;
    }

    public function setCards(array $cards)
    {
        $this->cards = new ArrayCollection($cards);
    }
}
