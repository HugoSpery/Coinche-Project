<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Repository\PartyRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/player_lobby.html.twig')]
final class PlayersLobby
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $code;

    public function __construct(private PartyRepository $partyRepository,private UserRepository $userRepository,private Security $security){

    }


    public function isRanked(){
        $party = $this->partyRepository->findOneBy(["code"=>$this->code]);
        return $party->isRanked();
    }

    public function getPartyState(){
        $party = $this->partyRepository->findOneBy(["code"=>$this->code]);
        return $party->isPublic();
    }

    public function getChiefPlayer(){
        $party = $this->partyRepository->findOneBy(["code"=>$this->code]);
        $chief = $party->getChief();
        return $chief;
    }

    public function getPlayersBlue() : array{
        $party = $this->partyRepository->findOneBy(["code"=>$this->code]);
        return $party->getTeamBlue()->toArray();
    }

    public function getPlayersRed() : array{
        $party = $this->partyRepository->findOneBy(["code"=>$this->code]);
        return $party->getTeamRed()->toArray();
    }

    public function getCurrentPlayer() : User {
        return $this->userRepository->findOneBy(["id"=>$this->security->getUser()->getId()]);
    }

}
