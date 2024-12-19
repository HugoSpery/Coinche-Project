<?php

namespace App\Service;

use App\Repository\FriendRequestRepository;
use App\Repository\PartyRequestRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class Menu
{

    public static function handleMenu(UserInterface $user, Request $request, UserRepository $userRepository, PaginatorInterface $paginator, FriendRequestRepository $friendRequestRepository,PartyRequestRepository $partyRequestRepository)
    {
        $name = $_GET['user'] ?? "" ;

        $requestAlreadyDone = $friendRequestRepository->getUsernameReceiverRequest([$user]);

        $users = $paginator->paginate(
            $userRepository->findUserSearch($name),
            $request->query->getInt('page_users', 1),
            5,
            ['pageParameterName' => 'page_users','queryParams' => $request->query->all()] // Nom unique pour cette pagination

        );


        $requestPartyAlreadyDone = $partyRequestRepository->getUsernameReceiverRequest([$user]);


        return [
            'requestAlreadyDone' => $requestAlreadyDone,
            'users' => $users,
            'requestPartyAlreadyDone' => $requestPartyAlreadyDone
        ];
    }
}