<?php

namespace App\Service\Message;

use App\Entity\Message;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MessageSearchProvider
{


    public function __construct(private MessageManager $messageManager,private Security $security,private HubInterface $hub)
    {
    }

    public function sendMessage($content){
        $message = new Message();
        $message->setContent($content);
        /** @var User $user */
        $user = $this->security->getUser();
        $message->setUserSender($user);
        $message->setNotification(false);
        $message->setLobby($user->getParty());

        $this->messageManager->save($message);
        
    }

    public function sendNotification($content,$lobby){
        $message = new Message();
        $message->setContent($content);
        $message->setNotification(true);
        $message->setLobby($lobby);

        $this->messageManager->save($message);

        $this->hub->publish(new Update(
            'https://example.com/NewMessage-'.$lobby->getCode(),
            json_encode(['message' => 'New message!'])
        ));

    }

}