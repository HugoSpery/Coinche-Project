<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

class CardPack
{
    private ?array $cards;


    public function __construct(CardRepository $cardRepository)
    {
        $this->cards = [];
        $cards = $cardRepository->findAll();
        foreach ($cards as $card) {
            $this->addCards($card);
        }
    }

    public function getCards(): ?array
    {
        return $this->cards;
    }

    public function addCards(Card $card): CardPack
    {
        $this->cards[] = $card;
        return $this;
    }

    public function shuffle(): CardPack
    {
        shuffle($this->cards);
        return $this;
    }


}
