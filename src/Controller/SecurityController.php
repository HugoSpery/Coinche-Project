<?php

namespace App\Controller;

use App\Repository\FriendRequestRepository;
use App\Repository\PartyRequestRepository;
use App\Repository\UserRepository;
use App\Service\Menu;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/user/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils,Security $security,Request $request,UserRepository $userRepository,PaginatorInterface $paginator,FriendRequestRepository $friendRequestRepository,PartyRequestRepository $partyRequestRepository): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $data = Menu::handleMenu($this->getUser(),$request,$userRepository,$paginator,$friendRequestRepository,$partyRequestRepository);


        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'requestAlreadyDone' => $data['requestAlreadyDone'],
            'users' => $data['users'],
            'requestPartyAlreadyDone' => $data['requestPartyAlreadyDone']
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout() : void
    {
    }
}
