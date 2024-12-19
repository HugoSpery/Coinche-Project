<?php

namespace App\Twig\Components;

use App\Entity\Game;
use App\Repository\GameRepository;
use App\Repository\PartyRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/game_component.html.twig')]
final class GameComponent
{
    use DefaultActionTrait;

    #[LiveProp()]
    public ?Game $game = null;

    public function __construct(private GameRepository $gameRepository,private RequestStack $requestStack,private UserRepository $userRepository){

    }
    #[LiveAction]
    public function getPlayers(){
        $playersList = $this->requestStack->getSession()->get('players');
        $newPlayers = new ArrayCollection();
        foreach ($playersList as $player){
            $newPlayer = $this->userRepository->findOneBy(['id'=>$player->getId()]);
            $newPlayer->getHand()->orderCard();
            $newPlayers->add($newPlayer);
        }
        $this->requestStack->getSession()->set('players',$newPlayers);
        return $newPlayers;
    }

    #[LiveAction]
    public function isStart() : bool{
        return $this->game->getLastRound()->isStart();
    }

}
