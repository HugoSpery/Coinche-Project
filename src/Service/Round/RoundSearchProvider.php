<?php

namespace App\Service\Round;

use App\Entity\Card;
use App\Entity\Round;
use App\Entity\User;
use App\Service\User\UserManager;

class RoundSearchProvider
{
    public function __construct(private UserManager $userManager){

    }

    public function strongestCardInHeap($heat){
        $strongestCard = $heat[0];
        foreach ($heat as $card){
            /** @var Card $card */
            if ($card->getPoint() > $strongestCard->getPoint()){
                $strongestCard = $card;
            }
        }
        return $strongestCard;
    }

    public function strongestCardBetweenAsset(Card $card1,Card $card2) : bool
    {
        if ($card1->getNumber() === 7 && $card2->getPointAsset() === 8) {
            return false;
        }
        if ($card1->getNumber() === 8 && $card2->getPointAsset() === 7){
            return true;
        }
        return $card1->getPointAsset() > $card2->getPointAsset();
    }

    public function isCut($heap,$typeAsset) : bool{
        foreach ($heap as $card){
            if ($card->getType()===$typeAsset){
                return true;
            }
        }
        return false;
    }


    public function strongestCardInHeapByType($heap,$typeAsset,$typeHeap){
        $strongestCard = [$heap[0],false];
        foreach ($heap as $card){
            /** @var Card $card */
            if ($card->getType() === $typeAsset && $strongestCard[1] === false ){
                $strongestCard = [$card,true];
            }elseif($card->getType() === $typeAsset && $strongestCard[1]===true && $card->getPointAsset() > $strongestCard[0]->getPointAsset()){
                $strongestCard = [$card,true];
            }elseif ($card->getType() === $typeHeap && $strongestCard[1] === false && $card->getPoint() > $strongestCard[0]->getPoint()){
                $strongestCard = [$card, false];
            }
        }
        return $strongestCard[0];
    }

    public function strongestCardInHeapAsset($heap,$typeAsset){
        $strongestCard = $heap[0];
        foreach ($heap as $card){
            /** @var Card $card */
            if ($card->getType() === $typeAsset && $card->getPointAsset() > $strongestCard->getPointAsset()){
                $strongestCard = $card;
            }
        }
        return $strongestCard;
    }

    public function deleteCardInHeapInPlayerHand($heap,$players){
        foreach ($players as $player){
            /** @var User $player */
            foreach ($heap as $card){
                if (in_array($card,$player->getHand()->getCards()->toArray())){
                    $player->getHand()->removeCard($card);
                }

            }
            $this->userManager->save($player);
        }
    }

    public function strongestAnnounceBetween($announce1,$announce2,$typeAsset) : bool{
        if ($announce1[0] === $announce2[0]){
            if ($announce1[1] !== $announce2[1]){
                return $announce1[1] > $announce2[1];
            }else{
                if ($announce1[2] === $typeAsset && $announce2[2] !== $typeAsset) {
                    return true;
                }elseif ($announce1[2] !== $typeAsset && $announce2[2] === $typeAsset) {
                    return false;
                }else{
                    //TODO : savoir qui a le contrat
                    return false;
                }
            }
        }

        $announce1P= $this->powerAnnounce($announce1[0]);
        $announce2P= $this->powerAnnounce($announce2[0]);

        return $announce1P > $announce2P;

    }

    private function powerAnnounce($announce){
        if ($announce === "Tierce") {
            return 0;
        }elseif ($announce === "Cinquante"){
            return 1;
        }
        elseif($announce === "Cent"){
            return 2;
        }
        return 3;
    }

    public function findAnnounces($cards){
        $announces = [];
        $succ = 1;
        for ($i = 0; $i < $cards->count(); $i++) {
            if ($i != 0) {
                $oldCard = $cards[$i - 1];
                if ($oldCard->getType() === $cards[$i]->getType() && (($oldCard->getNumber() + 1) === $cards[$i]->getNumber())) {
                    $succ++;
                    if ($succ === 5) {
                        foreach ($announces as $announce) {
                            if ($announce[0] === 'Cinquante' && $announce[1] === $oldCard->getNumber()) {
                                $announces = array_diff($announces, [$announce]);
                            }
                        }
                        $announces[] = ['Cent', $cards[$i]->getNumber(), $cards[$i]->getType()];
                    } elseif ($succ === 4) {
                        foreach ($announces as $announce) {
                            if ($announce[0] === 'Tierce' && $announce[1] === $oldCard->getNumber()) {
                                $announces = array_diff($announces, [$announce]);
                            }
                        }
                        $announces[] = ['Cinquante', $cards[$i]->getNumber(), $cards[$i]->getType()];
                    } elseif ($succ === 3) {
                        $announces[] = ['Tierce', $cards[$i]->getNumber(), $cards[$i]->getType()];
                    }

                } else {
                    $succ = 1;
                }
            }
        }


        $numbersCount = [];
        foreach ($cards as $card) {
            if (!isset($numbersCount[$card->getNumber()])) {
                $numbersCount[$card->getNumber()] = 0;
            }
            $numbersCount[$card->getNumber()]++;
        }

        foreach (array_keys($numbersCount) as $numberCount) {
            if ($numbersCount[$numberCount] === 4) {
                $announces[] = ['Carre', $numberCount];
            }
        }

        return $announces;
    }

}