<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\User;
use App\Enum\Number;
use App\Enum\Type;
use App\Repository\FriendRequestRepository;
use App\Repository\PartyRequestRepository;
use App\Repository\TeamRequestRepository;
use App\Repository\UserRepository;
use App\Service\Menu;
use App\Service\User\UserSearchProvider;
use Doctrine\ORM\EntityManagerInterface;
use http\Client\Request;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(TeamRequestRepository $teamRequestRepository,UserSearchProvider $userSearchProvider,EntityManagerInterface $em,PaginatorInterface $paginator,UserRepository $userRepository,\Symfony\Component\HttpFoundation\Request $request,PartyRequestRepository $partyRequestRepository): Response
    {

/*
        for($i=7;$i<=14;$i++){
            $card = new Card();

            $card->setNumber($i);
            $card->setType(Type::CLUBS);
            $card->setImageBack("/images/back.png");

            $card2 = new Card();

            $card2->setNumber($i);
            $card2->setType(Type::DIAMONDS);
            $card2->setImageBack("/images/back.png");

            $card3 = new Card();
            $card3->setNumber($i);
            $card3->setType(Type::HEARTS);
            $card3->setImageBack("/images/back.png");

            $card4 = new Card();
            $card4->setNumber($i);
            $card4->setType(Type::SPADES);
            $card4->setImageBack("/images/back.png");

            if ($i == 7){
                $card->setPoint(0);
                $card->setPointAsset(0);
                $card2->setPoint(0);
                $card2->setPointAsset(0);
                $card3->setPoint(0);
                $card3->setPointAsset(0);
                $card4->setPoint(0);
                $card4->setPointAsset(0);
            }else if ($i == 8) {
                $card->setPoint(0);
                $card->setPointAsset(0);
                $card2->setPoint(0);
                $card2->setPointAsset(0);
                $card3->setPoint(0);
                $card3->setPointAsset(0);
                $card4->setPoint(0);
                $card4->setPointAsset(0);
            }else if ($i == 9) {
                $card->setPoint(0);
                $card->setPointAsset(14);
                $card2->setPoint(0);
                $card2->setPointAsset(14);
                $card3->setPoint(0);
                $card3->setPointAsset(14);
                $card4->setPoint(0);
                $card4->setPointAsset(14);
            }else if ($i==10){
                $card->setPoint(10);
                $card->setPointAsset(10);
                $card2->setPoint(10);
                $card2->setPointAsset(10);
                $card3->setPoint(10);
                $card3->setPointAsset(10);
                $card4->setPoint(10);
                $card4->setPointAsset(10);
            }
            else if ($i==11){
                $card->setPoint(2);
                $card->setPointAsset(20);
                $card2->setPoint(2);
                $card2->setPointAsset(20);
                $card3->setPoint(2);
                $card3->setPointAsset(20);
                $card4->setPoint(2);
                $card4->setPointAsset(20);
            }
            else if ($i==12){
                $card->setPoint(3);
                $card->setPointAsset(3);
                $card2->setPoint(3);
                $card2->setPointAsset(3);
                $card3->setPoint(3);
                $card3->setPointAsset(3);
                $card4->setPoint(3);
                $card4->setPointAsset(3);
            }
            else if ($i==13){
                $card->setPoint(4);
                $card->setPointAsset(4);
                $card2->setPoint(4);
                $card2->setPointAsset(4);
                $card3->setPoint(4);
                $card3->setPointAsset(4);
                $card4->setPoint(4);
                $card4->setPointAsset(4);
            }
            else if ($i==14){
                $card->setPoint(11);
                $card->setPointAsset(11);
                $card2->setPoint(11);
                $card2->setPointAsset(11);
                $card3->setPoint(11);
                $card3->setPointAsset(11);
                $card4->setPoint(11);
                $card4->setPointAsset(11);
            }

            $em->persist($card);
            $em->persist($card2);
            $em->persist($card3);
            $em->persist($card4);

        }
        $em->flush();*/

        if (!$this->getUser()) {
            $userSearchProvider->createRandomUser();
        }
        return $this->render('home/index.html.twig', [

        ]);
    }

    #[Route('/see-request-friend', name: 'app_see_request_friend')]
    public function seeRequestFriend(){

        return $this->render("home/view_friend_request.html.twig");
    }

    #[Route('/see-friend-list', name: 'app_see_friend_list')]
    public function seeFriendList(){

        return $this->render("home/view_friend_list.html.twig");
    }

    #[Route('/see-user-list', name: 'app_see_user_list')]
    public function seeUserList(){

        return $this->render("home/view_user_list.html.twig");
    }

}
