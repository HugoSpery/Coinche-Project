<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Repository\MessageRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/chat_component.html.twig')]
final class ChatComponent
{
    use DefaultActionTrait;

    public function __construct(private MessageRepository $messageRepository,private Security $security){
    }

    #[LiveAction]
    public function getMessages(){
        /** @var User $user */
        $user = $this->security->getUser();
        if ($user->getParty() !== null){
            $user->getParty()->orderChatById();
            return $user->getParty()->getChat();
        }
        return [];
    }
}
