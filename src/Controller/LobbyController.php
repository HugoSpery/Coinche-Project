<?php

namespace App\Controller;

use App\Entity\Lobby;
use App\Entity\User;
use App\Exception\InvalidCodeException;
use App\Repository\FriendRequestRepository;
use App\Repository\PartyRepository;
use App\Repository\PartyRequestRepository;
use App\Repository\TeamRequestRepository;
use App\Repository\UserRepository;
use App\Service\Menu;
use App\Service\Message\MessageSearchProvider;
use App\Service\Party\PartyFormBuilder;
use App\Service\Party\PartyFormHandler;
use App\Service\Party\PartyManager;
use App\Service\Party\PartySearchProvider;
use App\Service\PartyRequest\PartyRequestManager;
use App\Service\TeamRequest\TeamRequestManager;
use App\Service\User\UserManager;
use InvalidArgumentException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;

class LobbyController extends AbstractController
{
    #[Route('/lobby/{code}', name: 'app_lobby')]
    public function lobby(string $code, PartySearchProvider $partySearchProvider,PartyRepository $partyRepository,MessageSearchProvider $messageSearchProvider, PartyManager $partyManager, HubInterface $hub,UserRepository $userRepository,PaginatorInterface $paginator,FriendRequestRepository $friendRequestRepository,Request $request,PartyRequestRepository $partyRequestRepository): Response
    {

        $update = new Update(
            'https://example.com/NewPlayer',
            json_encode(['message' => 'New player added!'])
        );

        $hub->publish($update);

        $party = $partyRepository->findOneBy(['code' => $code]);

        $messageSearchProvider->sendNotification('<span class="notif-new">'.$this->getUser()->getUsername().' a rejoint la partie !</span>',$party);


        if ($party === null || $party->getPlayers()->count() === 4) {
            return $this->redirectToRoute('app_home');
        }


        if (!$party->getPlayers()->contains($this->getUser())) {
            $party->addPlayer($this->getUser());
            $partySearchProvider->insertUserInTeam($this->getUser(),$party);
            $partyManager->save($party);
        }

        return $this->render('party/lobby.html.twig', [
            'party' => $party,

        ]);
    }

    #[Route('/join/party/', name: 'app_join_party')]
    public function joinParty(PartyRepository $partyRepository,PartyManager $partyManager,PartySearchProvider $partySearchProvider){
        $party = $partyRepository->findOneNotFullRanked();
        if ($party === []) {
            $party = new Lobby();
            $party->setPublic(true);
            $party->setRanked(true);
            $party->setChief(null);
            $party->addPlayer($this->getUser());
            $partySearchProvider->insertUserInTeam($this->getUser(),$party);

            $partyManager->save($party);
            return $this->redirectToRoute('app_lobby', ['code' => $party->getCode()]);
        }
        return $this->redirectToRoute('app_lobby', ['code' => $party[0]->getCode()]);

    }

    #[Route('/create/party', name: 'app_create_party')]
    public function createParty(PartyManager $partyManager, PartySearchProvider $partySearchProvider,PartyRepository $partyRepository,MessageSearchProvider $messageSearchProvider): Response
    {
        $partySearchProvider->quitUserFromParty($this->getUser());
        $party = new Lobby();
        $party->setPublic(true);
        $party->setRanked(false);

        $party->setChief($this->getUser());
        $party->addPlayer($this->getUser());
        $party->addTeamBlue($this->getUser());
        $partyManager->save($party);

        $messageSearchProvider->sendNotification('<span class="notif-new">'.$this->getUser()->getUsername().' a crée la partie !</span>',$party);

        return $this->redirectToRoute('app_lobby', ['code' => $party->getCode()]);
    }

    #[Route('/find/party', name: 'app_find_party')]
    public function findParty(PartyRepository $partyRepository, HubInterface $hub): Response
    {
        $party = $partyRepository->findOneNotFull();
        if ($party === []) {
            return $this->redirectToRoute('app_create_party');
        }


        return $this->redirectToRoute('app_lobby', ['code' => $party[0]->getCode()]);

    }

    #[Route('/quit/lobby', name: 'app_quit_lobby')]
    public function quitLobby(PartyManager $partyManager,PartyRepository $partyRepository, PartySearchProvider $partySearchProvider, HubInterface $hub,MessageSearchProvider $messageSearchProvider): Response
    {
        $user = $this->getUser();

        $messageSearchProvider->sendNotification('<span class="notif-left">'.$user->getUsername().' a quitté la partie !</span>',$user->getParty());

        $partySearchProvider->quitUserFromParty($user);

        $update = new Update(
            'https://example.com/NewPlayer',
            json_encode(['message' => 'Player Quit!'])
        );


        $hub->publish($update);

        return $this->redirectToRoute('app_home');
    }

    private function handleExceptionFindParty($e)
    {
        switch (get_class($e)) {
            case InvalidCodeException::class:
                $this->addFlash('error', $e->getMessage());
                break;
            case InvalidArgumentException::class:
                $this->addFlash('error', $e->getMessage());
                break;
            default:
                $this->addFlash('error', 'Une erreur est survenue');
        }
    }

    #[Route('/search/party', name: 'app_search_party')]
    public function searchParty(PartyFormHandler $partyFormHandler, PartyFormBuilder $partyFormBuilder, Request $request): Response
    {
        $form = $partyFormBuilder->buildFindPartyForm();

        try {
            $party = $partyFormHandler->handleFindPartyForm($form, $request);
            if ($party != null) {
                return $this->redirectToRoute('app_lobby', ['code' => $party->getCode()]);
            }
        } catch (\Exception $e) {
            $this->handleExceptionFindParty($e);
        }
        return $this->render('party/search.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/update/party/{code}', name: 'app_update_party')]
    public function updatePartyInfo(string $code,PartyRepository $partyRepository, PartySearchProvider $partySearchProvider, HubInterface $hub,PartyManager $partyManager){
        $party = $partyRepository->findOneBy(['code' => $code]);
        $data = json_decode(file_get_contents('php://input'), true);
        $input = $data['input'];
        if ($input === "public") {
            $party->setPublic(true);
        }else{
            $party->setPublic(false);
        }


        $partyManager->save($party);

        $hub->publish(new Update(
            'https://example.com/NewPlayer',
            json_encode(['message' => 'Party updated!'])
        ));

        return new JsonResponse('ok');



    }

    #[Route('/update/lobby/newChief',name : 'app_update_lobby_new_chief')]
    public function upgradePlayer(UserRepository $userRepository,PartyManager $partyManager,PartyRepository $partyRepository,HubInterface $hub){
        $data = json_decode(file_get_contents('php://input'), true);
        $code = $data['code'];
        $idChief = $data['id'];

        $party = $partyRepository->findOneBy(['code' => $code]);
        $chief = $userRepository->findOneBy(['id' => $idChief]);
        $party->setChief($chief);
        $partyManager->save($party);

        $hub->publish(new Update(
            'https://example.com/NewPlayer',
            json_encode(['message' => 'New chief!'])
        ));

        return new JsonResponse('ok');
    }

    #[Route('/update/lobby/kickPlayer',name : 'app_kick_player')]
    public function kickPlayer(UserRepository $userRepository,PartyManager $partyManager,PartyRepository $partyRepository,HubInterface $hub){
        $data = json_decode(file_get_contents('php://input'), true);
        $code = $data['code'];
        $idPlayer = $data['id'];

        $party = $partyRepository->findOneBy(['code' => $code]);
        $player = $userRepository->findOneBy(['id' => $idPlayer]);
        $party->removePlayer($player);

        $partyManager->save($party);

        $hub->publish(new Update(
            'https://example.com/KickedPlayer',
            json_encode($idPlayer)
        ));

        return new JsonResponse('ok');
    }

    #[Route('/update/stateUser',name : 'app_update_state_user')]
    public function updateUserState(UserRepository $userRepository,UserManager $userManager,HubInterface $hub){
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        /** @var User $user */
        $user = $userRepository->findOneBy(['id' => $id]);
        if ($user->isReady() == null){
            $user->setReady(false);
        }
        $user->setReady(!$user->isReady());
       $userManager->save($user);

        $hub->publish(new Update(
            'https://example.com/NewPlayer',
            json_encode(['message' => 'User state updated!'])
        ));

        $party = $user->getParty();
        if ($party->getPlayers()->count() == 4){
            foreach ($party->getPlayers() as $player){
                if (!$player->isReady()){
                    return new JsonResponse('ok');
                }
            }

            if ($party->getTeamRed()->count() != 2 && $party->getTeamBlue()->count() != 2){
                $user->setReady(false);
                $userManager->save($user);
                return new JsonResponse("interdit");
            }
            return new JsonResponse('start');
        }

        return new JsonResponse('ok');
    }

    #[Route('/update/userTeam',name : 'app_update_team')]
    public function updateUserTeam(UserRepository $userRepository,PartyManager $partyManager,HubInterface $hub){
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        /** @var User $user */
        $user = $userRepository->findOneBy(['id' => $id]);

        $party = $user->getParty();

        if ($party->getTeamBlue()->contains($user)){
            $party->removeTeamBlue($user);
            $party->addTeamRed($user);
        }else{
            $party->removeTeamRed($user);
            $party->addTeamBlue($user);
        }

        $partyManager->save($party);

        $hub->publish(new Update(
            'https://example.com/NewPlayer',
            json_encode(['message' => 'User team updated!'])
        ));

        return new JsonResponse('ok');
    }

    #[Route('/send/message',name : 'app_send_message')]
    public function sendMessage(MessageSearchProvider $provider,HubInterface $hub){

        $data = json_decode(file_get_contents('php://input'), true);
        $content = $data['message'];
        $provider->sendMessage($content);


        $hub->publish(new Update(
            'https://example.com/NewMessage-'.$this->getUser()->getParty()->getCode(),
            json_encode(['message' => 'New message!'])
        ));

        return new JsonResponse("ok");
    }

    #[Route('/get-info/party',name: 'app_get_info_party')]
    public function getInfoParty(PartyRepository $partyRepository){
        $party = $this->getUser()->getParty();
        return new JsonResponse([$party->getCode(),$this->getUser()->getId(),$this->getUser()->getUsername()]);
    }


}
