<?php

namespace App\Controller;

use App\Entity\FriendRequest;
use App\Entity\Lobby;
use App\Entity\PartyRequest;
use App\Entity\TeamRequest;
use App\Entity\User;
use App\Repository\FriendRequestRepository;
use App\Repository\PartyRepository;
use App\Repository\PartyRequestRepository;
use App\Repository\TeamRequestRepository;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use App\Service\Party\PartyManager;
use App\Service\Party\PartySearchProvider;
use App\Service\PartyRequest\PartyRequestManager;
use App\Service\Request\RequestManager;
use App\Service\TeamRequest\TeamRequestManager;
use App\Service\User\UserFormBuilder;
use App\Service\User\UserFormHandler;
use App\Service\User\UserManager;
use App\Service\User\UserSearchProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class UserController extends AbstractController
{
    #[Route('/send/request', name: 'app_send_request')]
    public function sendRequest(UserRepository $userRepository, UserManager $userManager, HubInterface $hub, RequestManager $requestManager, FriendRequestRepository $friendRequestRepository): Response
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $userTarget = $userRepository->findOneBy(['id' => $id]);
        if (!$userTarget) {
            return new JsonResponse("error");
        }

        $request = new FriendRequest();
        $request->setUserSender($this->getUser());
        $request->setUserReceiver($userTarget);
        $request->setDate(new \DateTime());

        $checkRequest = $friendRequestRepository->findOneBy(['userSender' => $this->getUser(), 'userReceiver' => $userTarget]);
        if ($checkRequest) {
            return new JsonResponse("error");
        }
        $checkRequest = $friendRequestRepository->findOneBy(['userSender' => $userTarget, 'userReceiver' => $this->getUser()]);

        if ($checkRequest) {
            $this->getUser()->addFriend($userTarget);
            $userTarget->addFriend($this->getUser());

            $userManager->save($this->getUser());
            $userManager->save($userTarget);

            $friendRequestRepository->delete($checkRequest);
            return new JsonResponse("ok");
        }


        $requestManager->save($request);

        $hub->publish(
            new Update(
                'https://example.com/NewRequest',
                json_encode($userTarget->getId())
            )
        );

        return new JsonResponse("ok");
    }

    #[Route('/send/remove-request', name: 'app_remove_send_request')]
    public function removeSendRequest(UserRepository $userRepository, HubInterface $hub, RequestManager $requestManager, FriendRequestRepository $friendRequestRepository): Response
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $userTarget = $userRepository->findOneBy(['id' => $id]);
        if (!$userTarget) {
            return new JsonResponse("error");
        }


        $request = $friendRequestRepository->findOneBy(['userSender' => $this->getUser(), 'userReceiver' => $userTarget]);
        if (!$request) {
            return new JsonResponse("error");
        }

        $requestManager->delete($request);

        return new JsonResponse("ok");
    }

    #[Route('/send/accept-request', name: 'app_accept_request')]
    public function acceptRequest(UserRepository $userRepository, UserManager $userManager, HubInterface $hub, RequestManager $requestManager, FriendRequestRepository $friendRequestRepository): Response
    {

        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $userTarget = $userRepository->findOneBy(['id' => $id]);
        if (!$userTarget) {
            return new JsonResponse("error1");
        }

        $request = $friendRequestRepository->findOneBy(['userSender' => $userTarget, 'userReceiver' => $this->getUser()]);
        if (!$request) {
            return new JsonResponse("error2");
        }

        $this->getUser()->addFriend($userTarget);
        $userTarget->addFriend($this->getUser());

        $userManager->save($this->getUser());
        $userManager->save($userTarget);
        $requestManager->delete($request);

        return new JsonResponse("ok");


    }

    #[Route('/send/refuse-request', name: 'app_refuse_request')]
    public function refuseRequest(UserRepository $userRepository, HubInterface $hub, RequestManager $requestManager, FriendRequestRepository $friendRequestRepository): Response
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $userTarget = $userRepository->findOneBy(['id' => $id]);
        if (!$userTarget) {
            return new JsonResponse("error");
        }

        $request = $friendRequestRepository->findOneBy(['userSender' => $userTarget, 'userReceiver' => $this->getUser()]);
        if (!$request) {
            return new JsonResponse("error");
        }

        $requestManager->delete($request);

        return new JsonResponse("ok");
    }

    #[Route('/send/delete-friend', name: 'app_delete_friend')]
    public function deleteFriend(UserRepository $userRepository, UserManager $userManager)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $userTarget = $userRepository->findOneBy(['id' => $id]);
        if (!$userTarget) {
            return new JsonResponse("error");
        }

        $this->getUser()->removeFriend($userTarget);
        $userTarget->removeFriend($this->getUser());

        $userManager->save($this->getUser());
        $userManager->save($userTarget);

        return new JsonResponse("ok");
    }

    #[Route('/send/invite-party', name: 'app_invite_party')]
    public function inviteParty(HubInterface $hub, TeamRequestManager $teamRequestManager, TeamRequestRepository $teamRequestRepository, UserRepository $userRepository, UserManager $userManager, PartyManager $partyManager, PartySearchProvider $partySearchProvider, PartyRequestManager $partyRequestManager, PartyRequestRepository $partyRequestRepository)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $location = $data['location'];
        $id = $data['id'];
        $userTarget = $userRepository->findOneBy(['id' => $id]);

        $checkInvite = $partyRequestRepository->findOneBy(['userSender' => $this->getUser(), 'userReceiver' => $userTarget]);

        if ($checkInvite) {
            return new JsonResponse("error");
        }

        $checkTeamInvite = $teamRequestRepository->findOneBy(['userSender' => $this->getUser(), 'userReceiver' => $userTarget]);
        if ($checkTeamInvite) {
            $teamRequestManager->delete($checkTeamInvite);
        }

        if (str_contains($location, 'lobby')) {
            $code = explode('/', $location)[2];
        } else {
            $partySearchProvider->quitUserFromParty($this->getUser());
            $party = new Lobby();
            $party->addPlayer($this->getUser());
            $partyManager->save($party);

            $code = $party->getCode();
        }

        $partyRequest = new PartyRequest();
        $partyRequest->setUserSender($this->getUser());
        $partyRequest->setUserReceiver($userTarget);

        $partyRequest->setDate(new \DateTime());

        $partyRequest->setCodeGame($code);


        $partyRequestManager->save($partyRequest);

        $hub->publish(
            new Update(
                'https://example.com/NewPartyRequest',
                json_encode([$this->getUser()->getUsername(), $this->getUser()->getId(), $userTarget->getUsername()])
            )
        );

        if (str_contains($location, 'lobby')) {
            return new JsonResponse("ok");
        } else {
            return new JsonResponse($code);
        }

    }


    #[Route('/send/remove-invite', name: 'app_remove_invite')]
    public function removeInvite(HubInterface $hub, UserRepository $userRepository, UserManager $userManager, PartyManager $partyManager, PartySearchProvider $partySearchProvider, PartyRequestManager $partyRequestManager, PartyRequestRepository $partyRequestRepository): JsonResponse
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $userTarget = $userRepository->findOneBy(['id' => $id]);

        $partyRequest = $partyRequestRepository->findOneBy(['userSender' => $this->getUser(), 'userReceiver' => $userTarget]);
        if (!$partyRequest) {
            return new JsonResponse("error");
        }

        $partyRequestManager->delete($partyRequest);

        return new JsonResponse("ok");
    }

    #[Route('/send/accept-invite', name: 'app_accept_invite')]
    public function acceptInvite(HubInterface $hub, UserRepository $userRepository, UserManager $userManager, PartyManager $partyManager, PartySearchProvider $partySearchProvider, PartyRequestManager $partyRequestManager, PartyRequestRepository $partyRequestRepository): JsonResponse
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];

        $partyRequest = $partyRequestRepository->findOneBy(['userSender' => $userRepository->findOneBy(['id' => $id]), 'userReceiver' => $this->getUser()]);
        if (!$partyRequest) {
            return new JsonResponse("error");
        }
        $code = $partyRequest->getCodeGame();

        $partyRequestManager->delete($partyRequest);

        return new JsonResponse($code);


    }

    #[Route('/get-info-user', name: 'app_get_info_user')]
    public function getInfoUser()
    {
        return new JsonResponse([$this->getUser()->getId(), $this->getUser()->getUsername()]);
    }

    private function getChartProfile(ChartBuilderInterface $chartBuilder, array $data): array
    {

        $tab = [];
        for ($i = 0; $i < count($data['gameWonEvolution']); $i++) {
            $tab[] = $i;
        }

        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $tab,
            'datasets' => [
                [
                    'label' => 'Parties jouées',
                    'backgroundColor' => 'rgb(65,105,225, .4)',
                    'borderColor' => 'rgb(0,0,139)',
                    'data' => $data['gameWonEvolution'],
                    'tension' => 0.4,
                ]
            ],
        ]);
        $chart->setOptions([
            'maintainAspectRatio' => false,
        ]);

        $chart2 = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart2->setData([
            'labels' => $tab,
            'datasets' => [
                [
                    'label' => 'Évolution des trophées',
                    'backgroundColor' => 'rgb(255,69,0, .4)',
                    'borderColor' => 'rgb(255,69,0)',
                    'data' => $data['trophyEvolution'],
                    'tension' => 0.4,

                ]
            ],
        ]);

        $chart2->setOptions([
            'maintainAspectRatio' => false,
        ]);

        return [$chart, $chart2];
    }

    #[Route('/profil', name: 'app_profile')]
    public function profile(UserSearchProvider $userSearchProvider, UserFormHandler $userFormHandler, UserFormBuilder $userFormBuilder, Request $request, ChartBuilderInterface $builder)
    {
        try {
            $this->denyAccessUnlessGranted(UserVoter::USE);
        } catch (\Exception $e) {
            return $this->redirectToRoute('app_home');
        }

        $form = $userFormBuilder->buildUpdateForm($this->getUser());

        try {
            $user = $userFormHandler->handleUpdateForm($request, $form);
            if ($user) {
                $this->addFlash('success', 'Your profile has been updated!');
                return $this->redirectToRoute('app_profile');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }


        $data = $userSearchProvider->getStatInfo($this->getUser());


        $charts = $this->getChartProfile($builder, $data);


        return $this->render("user/profile.html.twig", [
            'form' => $form,
            'chart' => $charts[0],
            'chart2' => $charts[1],
            'nbGamePlayed' => $data['nbGamePlayed'],
            'nbGameWon' => $data['nbGameWon'],
            'nbGameLost' => $data['nbGameLost'],
        ]);
    }

    #[Route('/profil/{id}', name: 'app_profile_other')]
    public function profileOther(User $user, UserSearchProvider $userSearchProvider, ChartBuilderInterface $builder, FriendRequestRepository $friendRequestRepository)
    {
        if (!$user || $user->getIsFake()) {
            return $this->redirectToRoute('app_home');
        }
        if ($user->getId() == $this->getUser()->getId()) {
            return $this->redirectToRoute('app_profile');
        }
        $data = $userSearchProvider->getStatInfo($user);
        $charts = $this->getChartProfile($builder, $data);
        $requestAlreadyDone = $friendRequestRepository->getUsernameReceiverRequest($this->getUser());

        return $this->render("user/profile.html.twig", [
            'form' => null,
            'user' => $user,
            'chart' => $charts[0],
            'chart2' => $charts[1],
            'nbGamePlayed' => $data['nbGamePlayed'],
            'nbGameWon' => $data['nbGameWon'],
            'nbGameLost' => $data['nbGameLost'],
            'requestAlreadyDone' => $requestAlreadyDone
        ]);
    }

    #[Route('/send/teamRequest', name: 'app_send_team_request')]
    public function sendTeamRequest(UserRepository $userRepository, TeamRequestRepository $teamRequestRepository, PartyRequestRepository $partyRequestRepository, PartyRequestManager $partyRequestManager, HubInterface $hub, TeamRequestManager $teamRequestManager)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $user = $userRepository->findOneBy(['id' => $id]);

        if (!$user) {
            return new JsonResponse('error');
        }

        $request = new TeamRequest();
        $request->setUserSender($this->getUser());
        $request->setUserReceiver($user);
        $request->setDate(new \DateTime());

        $checkRequest = $teamRequestRepository->findOneBy(['userSender' => $this->getUser(), 'userReceiver' => $user]);
        if ($checkRequest) {
            return new JsonResponse('error');
        }

        $checkRequest = $teamRequestRepository->findOneBy(['userSender' => $user, 'userReceiver' => $this->getUser()]);
        if ($checkRequest) {
            return new JsonResponse('error');
        }

        $partyRequest = $partyRequestRepository->findOneBy(['userSender' => $this->getUser(), 'userReceiver' => $user]);
        if ($partyRequest) {
            $partyRequestManager->delete($partyRequest);
        }

        $teamRequestManager->save($request);


        $hub->publish(
            new Update(
                'https://example.com/NewPartyRequest',
                json_encode([$this->getUser()->getUsername(), $this->getUser()->getId(), $user->getUsername()])
            )
        );

        return new JsonResponse('ok');

    }


    #[Route('/send/removeTeamRequest', name: 'app_send_remove_team_request')]
    public function removeTeamRequest(UserRepository $userRepository, TeamRequestRepository $teamRequestRepository, PartyRequestRepository $partyRequestRepository, PartyRequestManager $partyRequestManager, HubInterface $hub, TeamRequestManager $teamRequestManager)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $user = $userRepository->findOneBy(['id' => $id]);
        if(!$user){
            return new JsonResponse('error');
        }
        $request = $teamRequestRepository->findOneBy(['userSender' => $this->getUser(), 'userReceiver' => $user]);

        if(!$request){
            return new JsonResponse('error');
        }

        $partyRequestManager->delete($request);

        return new JsonResponse('ok');
    }

    #[Route('/send/acceptTeamRequest', name: 'app_send_accept_team_request')]
    public function acceptTeamRequest(PartySearchProvider $partySearchProvider,UserRepository $userRepository,PartyManager $partyManager,PartyRepository $partyRepository, TeamRequestRepository $teamRequestRepository, PartyRequestRepository $partyRequestRepository, PartyRequestManager $partyRequestManager, HubInterface $hub, TeamRequestManager $teamRequestManager)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $user = $userRepository->findOneBy(['id' => $id]);
        if(!$user){
            return new JsonResponse('error');
        }

        $request = $teamRequestRepository->findOneBy(['userSender' => $user, 'userReceiver' => $this->getUser()]);

        if(!$request){
            return new JsonResponse('error');
        }

        $partySearchProvider->quitUserFromParty($this->getUser());
        $partySearchProvider->quitUserFromParty($user);


        $lobbyInfo = $partyRepository->findOneForTeam();
        if (!$lobbyInfo) {
            $lobby = new Lobby();
            $lobby->addPlayer($this->getUser());
            $lobby->addPlayer($user);
            $lobby->setPublic(true);
            $lobby->setRanked(true);
            $lobby->addTeamBlue($this->getUser());
            $lobby->addTeamBlue($user);
            $partyManager->save($lobby);
        }else{
            if ($lobbyInfo[1] === "blue"){
                $lobbyInfo[0]->addPlayer($this->getUser());
                $lobbyInfo[0]->addTeamBlue($this->getUser());
                $lobbyInfo[0]->addPlayer($user);
                $lobbyInfo[0]->addTeamBlue($user);
            }else{
                $lobbyInfo[0]->addPlayer($this->getUser());
                $lobbyInfo[0]->addTeamRed($this->getUser());
                $lobbyInfo[0]->addPlayer($user);
                $lobbyInfo[0]->addTeamRed($user);
            }
            $partyManager->save($lobbyInfo[0]);
            $lobby = $lobbyInfo[0];

        }




        $hub->publish(
            new Update(
                'https://example.com/AcceptedTeamRequest-'.$user->getId(),
                json_encode($lobby->getCode())
            )
        );

        $teamRequestManager->delete($request);

        return new JsonResponse($lobby->getCode());
    }

    #[Route('/send/refuseTeamRequest', name: 'app_send_refuse_team_request')]
    public function refuseTeamRequest(UserRepository $userRepository, TeamRequestRepository $teamRequestRepository, PartyRequestRepository $partyRequestRepository, PartyRequestManager $partyRequestManager, HubInterface $hub, TeamRequestManager $teamRequestManager)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $user = $userRepository->findOneBy(['id' => $id]);
        if(!$user){
            return new JsonResponse('error');
        }
        $request = $teamRequestRepository->findOneBy(['userSender' => $user, 'userReceiver' => $this->getUser()]);

        if(!$request){
            return new JsonResponse('error');
        }

        $partyRequestManager->delete($request);

        return new JsonResponse('ok');
    }
}
