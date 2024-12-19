<?php

namespace App\Service\Message;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;

class MessageManager
{

    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function save(Message $message)
    {
        $this->em->persist($message);
        $this->em->flush();
    }

    public function delete(Message $message)
    {
        $this->em->remove($message);
        $this->em->flush();
    }

}