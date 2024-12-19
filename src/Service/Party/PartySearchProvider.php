<?php

namespace App\Service\Party;

use App\Entity\Lobby;
use App\Entity\User;
use App\Repository\PartyRepository;
use App\Repository\UserRepository;
use App\Service\Message\MessageManager;
use Symfony\Bundle\SecurityBundle\Security;

class PartySearchProvider
{

    public function __construct(private PartyRepository $partyRepository ,private Security $security, private PartyManager $partyManager,private MessageManager $messageManager)
    {
    }

    public function quitUserFromParty(User $user)
    {
        /** @var Lobby $party */
        $party = $this->security->getUser()->getParty();
        if ($party === null) {
            foreach ($this->partyRepository->findAll() as $p){
                if ($p->getChief() === $this->security->getUser()){
                    if ($p->getPlayers()->count() === 0) {
                        foreach ($p->getChat() as $message){
                            $this->messageManager->delete($message);
                        }
                        $this->partyManager->delete($p);
                    } else {
                        $p->setChief($p->getPlayers()->first());
                        $this->partyManager->save($p);
                    }
                }
            }
            return;
        }
        $party->removePlayer($user);
        $this->partyManager->save($party);
        if ($party->getPlayers()->count() === 0) {
            foreach ($party->getChat() as $message){
                $this->messageManager->delete($message);
            }
            $this->partyManager->delete($party);
        } else {
            $this->partyManager->save($party);
        }


    }

    public function insertUserInTeam(User $user, Lobby $lobby){
        if ($lobby->getTeamBlue()->count() == 2){
            $lobby->addTeamRed($user);
            return;
        }
        if ($lobby->getTeamRed()->count() == 2){
            $lobby->addTeamBlue($user);
            return;
        }

        if($lobby->getTeamBlue()->count() > $lobby->getTeamRed()->count()){
            $lobby->addTeamRed($user);
        }else{
            $lobby->addTeamBlue($user);
        }
    }

}