<?php

namespace App\Controller;

use App\Entity\CardPack;
use App\Entity\Game;
use App\Entity\Hand;
use App\Entity\Lobby;
use App\Entity\Round;
use App\Enum\Type;
use App\Exception\InvalidCodeException;
use App\Repository\CardRepository;
use App\Repository\FriendRequestRepository;
use App\Repository\GameRepository;
use App\Repository\PartyRepository;
use App\Repository\PartyRequestRepository;
use App\Repository\UserRepository;
use App\Service\Game\GameManager;
use App\Service\Menu;
use App\Service\Party\PartyFormBuilder;
use App\Service\Party\PartyFormHandler;
use App\Service\Party\PartyManager;
use App\Service\Party\PartySearchProvider;
use App\Service\Round\RoundManager;
use App\Service\Round\RoundSearchProvider;
use App\Service\User\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Knp\Component\Pager\PaginatorInterface;
use phpDocumentor\Reflection\Types\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;

class PartyController extends AbstractController
{

    #[Route('/launch/party/', name: 'app_launch_party')]
    public function launchParty(GameRepository $gameRepository, GameManager $gameManager, UserManager $userManager, HubInterface $hub, CardRepository $cardRepository, Environment $environment, RoundManager $roundManager): Response
    {

        $user = $this->getUser();
        /** @var Lobby $party */
        $party = $user->getParty();

        $hub->publish(new Update(
            'https://example.com/LaunchParty-'.$party->getCode()
        ));

        $players = $party->getPlayers();
        if (count($players) != 4) {
            $this->addFlash("error", "Il faut 4 joueurs pour commencer la partie");
            return $this->redirectToRoute('app_lobby', ['code' => $party->getCode()]);
        }

        $game = $gameRepository->findOneBy(['code' => $party->getCode()]);
        if ($game !== null) {
            return $this->redirectToRoute('app_party', ['code' => $party->getCode()]);
        }

        $game = new Game();

        $game->addPlayer($party->getTeamRed()[0]);
        $game->addPlayer($party->getTeamBlue()[0]);
        $game->addPlayer($party->getTeamRed()[1]);
        $game->addPlayer($party->getTeamBlue()[1]);


        $game->addTeamRed($party->getTeamRed()[0]);
        $game->addTeamRed($party->getTeamRed()[1]);
        $game->addTeamBlue($party->getTeamBlue()[0]);
        $game->addTeamBlue($party->getTeamBlue()[1]);

        $game->setCode($party->getCode());
        $game->setPointsBlue(0);
        $game->setPointsRed(0);
        $turn = (bool)rand(0, 1);
        $game->setTurnBlue($turn);

        if ($turn) {
            $game->getTeamBlue()[0]->setIsStarting(true);
            $game->getTeamRed()[0]->setIsStarting(false);
        } else {
            $game->getTeamRed()[0]->setIsStarting(true);
            $game->getTeamBlue()[0]->setIsStarting(false);

        }
        $game->getTeamBlue()[1]->setIsStarting(false);
        $game->getTeamRed()[1]->setIsStarting(false);


        $cardPack = new CardPack($cardRepository);
        $cardPack->shuffle();
        $cards = $cardPack->getCards();

        for ($i = 0; $i < 4; $i++) {
            $hand = new Hand();
            $hand->setCards([$cards[$i], $cards[$i + 4], $cards[$i + 8], $cards[$i + 12], $cards[$i + 16], $cards[$i + 20], $cards[$i + 24], $cards[$i + 28]]);
            $hand->orderCard();
            $game->getPlayers()[$i]->setHand($hand);
            $userManager->save($game->getPlayers()[$i]);
        }

        $round = new Round();
        $round->setCpt(0);
        $round->setPointsRed(0);
        $round->setPointsBlue(0);
        $round->setAnnounceRed(0);
        $round->setAnnounceBlue(0);
        $round->setAnnounceName([]);

        if ($game->getTeamRed()[0]->isStarting()) {
            $round->setStartPlayer($game->getTeamRed()[0]);
            $round->setWaitingUser($game->getTeamRed()[0]);
        } else {
            $round->setWaitingUser($game->getTeamBlue()[0]);
            $round->setStartPlayer($game->getTeamBlue()[0]);
        }

        $round->setStart(false);

        $roundManager->save($round);

        $game->addRound($round);
        $game->setEnd(false);

        $gameManager->save($game);




        return $this->redirectToRoute('app_party', ['code' => $party->getCode()]);
    }

    #[Route('/party/{code}', name: 'app_party')]
    public function party(string $code, GameRepository $gameRepository, RequestStack $requestStack, Environment $environment)
    {
        $game = $gameRepository->findOneBy(['code' => $code]);

        if (in_array($this->getUser(), $game->getTeamRed()->toArray())) {
            $team = 'red';
        } else {
            $team = 'blue';
        }
        $environment->addGlobal('team', $team);

        $players = new ArrayCollection();


        if ($this->getUser()->getUsername() == $game->getTeamRed()[0]->getUsername()){
            $players->add($game->getTeamRed()[0]);
            $players->add($game->getTeamBlue()[0]);
            $players->add($game->getTeamRed()[1]);
            $players->add($game->getTeamBlue()[1]);
        }elseif ($this->getUser()->getUsername() == $game->getTeamRed()[1]->getUsername()) {
            $players->add($game->getTeamRed()[1]);
            $players->add($game->getTeamBlue()[1]);
            $players->add($game->getTeamRed()[0]);
            $players->add($game->getTeamBlue()[0]);
        }elseif ($this->getUser()->getUsername() == $game->getTeamBlue()[0]->getUsername()) {
            $players->add($game->getTeamBlue()[0]);
            $players->add($game->getTeamRed()[1]);
            $players->add($game->getTeamBlue()[1]);
            $players->add($game->getTeamRed()[0]);

        }else{
            $players->add($game->getTeamBlue()[1]);
            $players->add($game->getTeamRed()[0]);
            $players->add($game->getTeamBlue()[0]);
            $players->add($game->getTeamRed()[1]);
        }

        $session = $requestStack->getSession();

        // Stocker la variable
        $session->set('players', $players);


        $environment->addGlobal('players', $players);

        return $this->render('game/index.html.twig', [
            'game' => $game,
            'players' => $players
        ]);

    }

    #[Route('/game/choose-atout/{code}', name: 'app_choose_atout')]
    public function chooseAtout(string $code, GameRepository $gameRepository, Security $security, UserManager $userManager, RequestStack $requestStack)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $nextPlayer = $data['nextPlayer'];
        $players = $requestStack->getSession()->get('players');


        $playerStarting = null;
        foreach ($players as $player) {
            //$userManager->save($player);
            if ($player->isStarting()) {
                $playerStarting = $player;
            }
        }

        if ($nextPlayer !== null) {
            $nextPlayer = substr($nextPlayer, 1, -1);
            foreach ($players as $player) {
                if ($player->getUsername() === $nextPlayer) {
                    $player->setIsStarting(true);
                    $playerStarting = $player;
                } else {
                    $player->setIsStarting(false);
                }
            }

            $requestStack->getSession()->set('players', $players);

        }


        if ($playerStarting->getUsername() === $security->getUser()->getUserName()) {
            return new JsonResponse(['message' => 'your turn']);
        }

        return new JsonResponse(['message' => $playerStarting->getUsername()]);
    }

    #[Route('/game/nextChooser/{code}', name: 'app_next_chooser')]
    public function nextChooser(string         $code, RequestStack $requestStack, GameRepository $gameRepository,
                                GameManager    $gameManager, HubInterface $hub, RoundManager $roundManager,
                                CardRepository $cardRepository, UserManager $userManager)
    {

        $data = json_decode(file_get_contents('php://input'), true);
        $choosePoint = $data['choosePoint'];
        $chooseType = $data['chooseType'];

        $game = $gameRepository->findOneBy(['code' => $code]);
        $round = $game->getLastRound();
        $players = $requestStack->getSession()->get('players');


        $players[0]->setIsStarting(false);

        $players[1]->setIsStarting(true);

        $players[2]->setIsStarting(false);

        $players[3]->setIsStarting(false);

        foreach ($game->getPlayers() as $player) {
            if ($player->getUsername() === $players[0]->getUsername()) {
                $player0 = $player;
            }
            if ($player->getUsername() === $players[1]->getUsername()) {
                $player1 = $player;
            }
            if ($player->getUsername() === $players[2]->getUsername()) {
                $player2 = $player;
            }
        }


        if ($round->getCpt() === 3 && $round->getType() == null && $round->getPoints() == null && $chooseType == null && $choosePoint == null) {

            $cardPack = new CardPack($cardRepository);
            $cardPack->shuffle();
            $cards = $cardPack->getCards();

            for ($i = 0; $i < 4; $i++) {
                $hand = new Hand();
                $hand->setCards([$cards[$i], $cards[$i + 4], $cards[$i + 8], $cards[$i + 12], $cards[$i + 16], $cards[$i + 20], $cards[$i + 24], $cards[$i + 28]]);
                $hand->orderCard();
                $game->getPlayers()[$i]->setHand($hand);
                $userManager->save($game->getPlayers()[$i]);
            }

            $players[1]->setIsStarting(false);

            $players[2]->setIsStarting(true);

            $round = new Round();
            $round->setCpt(0);
            $round->setWaitingUser($player2);
            $game->addRound($round);
            $roundManager->save($round);
            $gameManager->save($game);
        } else if ($round->getCpt() === 3 && $round->getType() != null && $round->getPoints() != null && $chooseType == null && $choosePoint == null) {

            $players[1]->setIsStarting(false);

            $round->setWaitingUser($round->getStartPlayer());
            $round->setStart(true);

            $roundManager->save($round);

            $update = new Update(
                'https://example.com/startRound-' . $game->getCode(),
                json_encode($round->getStartPlayer()->getUsername())
            );

            $hub->publish($update);

            return new JsonResponse(['round' => $round->getCpt(), 'message' => $players[2]->getUsername()]);

        } else {
            $round->setCpt($round->getCpt() + 1);
            $round->setWaitingUser($player1);
            $roundManager->save($round);
        }

        if ($choosePoint != null && $chooseType != null) {
            if ($chooseType === "diamond") {
                $round->setType(Type::DIAMONDS);
            } elseif ($chooseType === "heart") {
                $round->setType(Type::HEARTS);
            } elseif ($chooseType === "spade") {
                $round->setType(Type::SPADES);
            } else {
                $round->setType(Type::CLUBS);
            }
            $round->setPoints($choosePoint);
            $round->setCpt(1);
            $round->setPlayer($player0);
        }

        $roundManager->save($round);

        $requestStack->getSession()->set('players', $players);


        if ($players[2]->IsStarting()) {
            $update = new Update(
                'https://example.com/NewChooser-' . $game->getCode(),
                json_encode($players[2]->getUsername())
            );

            $hub->publish($update);
            return new JsonResponse(['round' => $round->getCpt(), 'message' => $players[2]->getUsername()]);
        }


        $update = new Update(
            'https://example.com/NewChooser-'.$game->getCode(),
            json_encode($players[1]->getUsername())
        );

        $hub->publish($update);

        return new JsonResponse(['round' => $round->getCpt(), 'message' => $players[1]->getUsername()]);

    }

    #[Route('/game/playCard/{code}', name: 'app_play_card')]
    public function chooseCard(string      $code, RequestStack $requestStack, GameRepository $gameRepository,RoundSearchProvider $roundSearchProvider,
                               GameManager $gameManager, HubInterface $hub, RoundManager $roundManager, CardRepository $cardRepository,UserRepository $userRepository)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $type = $data['type'];
        $number = $data['number'];
        $card = $cardRepository->findOneBy(['type' => $type, 'number' => $number]);

        $game = $gameRepository->findOneBy(['code' => $code]);
        $round = $game->getLastRound();


        $round->addHeap($card);

        $players = $requestStack->getSession()->get('players');


        $roundManager->save($round);
        $gameManager->save($game);


        if ($round->getHeap()->count() === 4) {

            $roundManager->save($round);
            $gameManager->save($game);


            $update = new Update(
                'https://example.com/EndRound-'.$game->getCode(),
                json_encode([$players[1]->getUsername(),$players[0]->getUsername(),[$card->getType()->value,$card->getNumber()]])
            );

            $hub->publish($update);

            return new JsonResponse($round->getHeap()->last());
        }

        $round->setWaitingUser($userRepository->findOneBy(['username' => $players[1]->getUsername()]));

        $hub->publish(new Update(
            'https://example.com/PlayCard-'.$game->getCode(),
            json_encode([$players[1]->getUsername(),[$card->getType()->value,$card->getNumber()],$players[0]->getUsername()])
        ));

        return new JsonResponse($round->getHeap()->last());
    }

    #[Route('/game/possibleCard/{code}', name: 'app_possible_card')]
    public function possibleCard(string              $code, RequestStack $requestStack, GameRepository $gameRepository,
                                 UserRepository      $userRepository, HubInterface $hub, RoundManager $roundManager, CardRepository $cardRepository,
                                 RoundSearchProvider $roundSearchProvider)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $game = $gameRepository->findOneBy(['code' => $code]);
        $round = $game->getLastRound();
        $typeRound = $round->getType();
        $heap = $round->getHeap();
        $player = $userRepository->findOneBy(['username' => $data['player']]);
        $playerCards = $player->getHand()->getCards();
        $possibleCards = [];


        if ($heap->count() === 0) {
            $cardsJson = [];
            foreach ($playerCards as $card) {
                $cardsJson[] = ['number' => $card->getNumber(), 'type' => $card->getType()];
            }
            return new JsonResponse(json_encode($cardsJson));
        }


        $typeHeap = $heap[0]->getType();

        $secondChoice = [];

        if ($typeHeap === $typeRound) {
            foreach ($playerCards as $card) {
                if ($card->getType() === $typeRound) {
                    if ($roundSearchProvider->strongestCardBetweenAsset($card, $heap->last())) {
                        $possibleCards[] = ['number' => $card->getNumber(), 'type' => $card->getType()];
                    } else {
                        $secondChoice[] = ['number' => $card->getNumber(), 'type' => $card->getType()];
                    }
                }
            }
            if (empty($possibleCards)) {
                if (empty($secondChoice)) {
                    $cardsJson = [];
                    foreach ($playerCards as $card) {
                        $cardsJson[] = ['number' => $card->getNumber(), 'type' => $card->getType()];
                    }
                    return new JsonResponse(json_encode($cardsJson));
                }
                return new JsonResponse(json_encode($secondChoice));
            }
            return new JsonResponse(json_encode($possibleCards));
        }


        $strongestCardInHeap = $roundSearchProvider->strongestCardInHeap($heap);

        if (in_array($this->getUser(), $game->getTeamRed()->toArray())) {
            $teamPlayer = 'red';
        } else {
            $teamPlayer = 'blue';
        }

        foreach ($game->getPlayers() as $player) {
            if (in_array($strongestCardInHeap, $player->getHand()->getCards()->toArray())) {
                if (in_array($player, $game->getTeamRed()->toArray())) {
                    $teamStrongest = 'red';
                } else {
                    $teamStrongest = 'blue';
                }
            }
        }

        foreach ($playerCards as $card) {
            if ($card->getType() === $typeHeap) {
                $possibleCards[] = ['number' => $card->getNumber(), 'type' => $card->getType()];
            }
            if ($card->getType() === $typeRound) {
                $secondChoice[] = ['number' => $card->getNumber(), 'type' => $card->getType()];
            }
        }
        $playerCardsJson = [];
        foreach ($playerCards as $card) {
            $playerCardsJson[] = ['number' => $card->getNumber(), 'type' => $card->getType()];
        }

        if (empty($possibleCards)) {
            if (empty($secondChoice)) {
                return new JsonResponse(json_encode($playerCardsJson));
            } else {
                if ($roundSearchProvider->isCut($heap, $typeRound)) {
                    return new JsonResponse(json_encode($playerCardsJson));
                }
                if ($teamPlayer === $teamStrongest) {
                    return new JsonResponse(json_encode($playerCardsJson));
                }
                return new JsonResponse(json_encode($secondChoice));
            }
        }

        return new JsonResponse(json_encode($possibleCards));

    }

    #[Route('/game/endHeap/{code}', name: 'app_end_heap')]
    public function endHeap(string              $code, RequestStack $requestStack, GameRepository $gameRepository,
                            UserRepository      $userRepository, HubInterface $hub, RoundManager $roundManager, GameManager $gameManager,
                            RoundSearchProvider $roundSearchProvider, CardRepository $cardRepository, UserManager $userManager)
    {
        $game = $gameRepository->findOneBy(['code' => $code]);
        $round = $game->getLastRound();
        $heap = $round->getHeap();
        $typeRound = $round->getType();

        $typeHeap = $heap[0]->getType();

        $tt = 0;

        foreach ($heap as $card) {
            if ($card->getType() === $typeRound) {
                $tt = $tt + $card->getPointAsset();
            } else {
                $tt = $tt + $card->getPoint();
            }
        }

        if ($typeHeap === $typeRound) {
            $strongestCard = $roundSearchProvider->strongestCardInHeapAsset($heap, $typeRound);
        } else {
            $strongestCard = $roundSearchProvider->strongestCardInHeapByType($heap, $typeRound, $typeHeap);
        }

        if ($game->getPlayers()[0]->getHand()->getCards()->count() == 1) {
            $tt = $tt + 10;
        }

        foreach ($game->getPlayers() as $player) {
            if (in_array($strongestCard, $player->getHand()->getCards()->toArray())) {
                $strongestPlayer = $player;
                if (in_array($player, $game->getTeamRed()->toArray())) {
                    $round->setPointsRed($round->getPointsRed() + $tt);
                } else {
                    $round->setPointsBlue($round->getPointsBlue() + $tt);
                }
            }
        }


        $roundSearchProvider->deleteCardInHeapInPlayerHand($heap, $game->getPlayers());

        $round->setHeap(new ArrayCollection());
        $round->setStartPlayer($strongestPlayer);

        $roundManager->save($round);
        $gameManager->save($game);

        if ($game->getPlayers()[0]->getHand()->getCards()->count() == 0) {

            $update = new Update(
                'https://example.com/finishedRound-'.$game->getCode(),
                json_encode($strongestPlayer->getUsername())
            );


        } else {
            $update = new Update(
                'https://example.com/startRound-'.$game->getCode(),
                json_encode($strongestPlayer->getUsername())
            );

        }
        $hub->publish($update);

        return new JsonResponse($strongestPlayer->getUsername());

    }

    #[Route('/game/endRound/{code}', name: 'app_end_round')]
    function endRound(string                 $code, RequestStack $requestStack, GameRepository $gameRepository,
                      UserRepository         $userRepository, HubInterface $hub, RoundManager $roundManager, GameManager $gameManager,
                      EntityManagerInterface $em, CardRepository $cardRepository, UserManager $userManager)
    {

        $game = $gameRepository->findOneBy(['code' => $code]);
        $round = $game->getLastRound();

        $playerGo = $round->getPlayer();

        if (in_array($playerGo, $game->getTeamRed()->toArray())) {
            $teamGo = "red";
        } else {
            $teamGo = "blue";
        }

        $round->setPointsRed($round->getPointsRed() + $round->getAnnounceRed());
        $round->setPointsBlue($round->getPointsBlue() + $round->getAnnounceBlue());

        if ($teamGo === "red" && $round->getPoints() > $round->getPointsRed()) {
            $game->setPointsBlue($game->getPointsBlue() + 160 + $round->getPoints() + $round->getAnnounceRed());
        } elseif ($teamGo === "blue" && $round->getPoints() > $round->getPointsBlue()) {
            $game->setPointsRed($game->getPointsRed() + 160 + $round->getPoints() + $round->getAnnounceBlue());
        } elseif ($teamGo === "red" && $round->getPoints() < $round->getPointsRed()) {
            $game->setPointsRed($game->getPointsRed() + $round->getPoints() + $round->getPointsRed());
            $game->setPointsBlue($game->getPointsBlue() + $round->getPointsBlue());
        } elseif ($teamGo === "blue" && $round->getPoints() < $round->getPointsBlue()) {
            $game->setPointsBlue($game->getPointsBlue() + $round->getPoints() + $round->getPointsBlue());
            $game->setPointsRed($game->getPointsRed() + $round->getPointsRed());
        }


        $newRound = new Round();
        $newRound->setCpt(0);
        $newRound->setStart(false);
        $newRound->setPointsRed(0);
        $newRound->setPointsBlue(0);

        $startPlayerLastRound = $round->getStartPlayer();

        $cardPack = new CardPack($cardRepository);
        $cardPack->shuffle();
        $cards = $cardPack->getCards();


        for ($i = 0; $i < 4; $i++) {
            $hand = new Hand();
            $hand->setCards([$cards[$i], $cards[$i + 4], $cards[$i + 8], $cards[$i + 12], $cards[$i + 16], $cards[$i + 20], $cards[$i + 24], $cards[$i + 28]]);
            $hand->orderCard();
            $game->getPlayers()[$i]->setHand($hand);
            $userManager->save($game->getPlayers()[$i]);
        }

        $players = $requestStack->getSession()->get('players');
        $indexNextPlayer = 0;
        for ($i = 0; $i < 4; $i++) {
            if ($players[$i]->getUsername() === $startPlayerLastRound->getUsername()) {
                $indexNextPlayer = $i;
            }
        }


        if ($indexNextPlayer===3){
            $indexNextPlayer = 0;
        }else{
            $indexNextPlayer = $indexNextPlayer + 1;
        }


        foreach ($game->getPlayers() as $player) {
            if ($player->getUsername() === $players[$indexNextPlayer]->getUsername()){
                $nextPlayer = $player;
            }

        }


        $requestStack->getSession()->set('players',$players);

        $newRound->setStartPlayer($nextPlayer);

        $newRound->setWaitingUser($nextPlayer);

        $roundManager->save($newRound);

        $game->addRound($newRound);

        $gameManager->save($game);

        if ($game->getPointsRed() >= 500 || $game->getPointsBlue() >= 500){
            $game->setEnd(true);
            $gameManager->save($game);

            $update = new Update(
                'https://example.com/EndGame-'.$game->getCode(),
            );
        }
        else{
            $update = new Update(
                'https://example.com/NewChooser-'.$game->getCode(),
                json_encode($players[$indexNextPlayer]->getUsername())
            );
        }

        $hub->publish($update);


        return new JsonResponse("ok");
    }


    #[Route('/game/checkAnnounce/{code}', name: 'app_check_announce')]
    public function checkAnnounce(string              $code, RequestStack $requestStack, GameRepository $gameRepository,
                                  UserRepository      $userRepository, HubInterface $hub, RoundManager $roundManager, GameManager $gameManager,
                                  RoundSearchProvider $roundSearchProvider, CardRepository $cardRepository, UserManager $userManager)
    {

        $game = $gameRepository->findOneBy(['code' => $code]);
        $round = $game->getLastRound();
        $players = $requestStack->getSession()->get('players');

        $hand = $userRepository->findOneBy(['username' => $players[0]->getUsername()])->getHand();
        $hand->orderCard();
        $cards = $hand->getCards();


        $announces = $roundSearchProvider->findAnnounces($cards);

        if (empty($announces)) {
            return new JsonResponse(json_encode("vide"));
        }

        return new JsonResponse(json_encode($announces));
    }

    #[Route('/game/announce/{code}', name: 'app_announce')]
    public function announce(string              $code, RequestStack $requestStack, GameRepository $gameRepository,
                             UserRepository      $userRepository, HubInterface $hub, RoundManager $roundManager, GameManager $gameManager,
                             RoundSearchProvider $roundSearchProvider, CardRepository $cardRepository, UserManager $userManager)
    {

        $data = json_decode(file_get_contents('php://input'), true);
        $announces = $this->checkAnnounce($code, $requestStack, $gameRepository, $userRepository, $hub, $roundManager, $gameManager, $roundSearchProvider, $cardRepository, $userManager);
        $announces = json_decode($announces->getContent());
        $announces = json_decode($announces);

        if ($announces === "vide") {
            return new JsonResponse("vide");
        }

        $game = $gameRepository->findOneBy(['code' => $code]);
        $round = $game->getLastRound();
        $players = $requestStack->getSession()->get('players');

        $player = $userRepository->findOneBy(['username' => $players[0]->getUsername()]);

        $hand = $player->getHand();
        $cards = $hand->getCards();

        if (in_array($player, $game->getTeamRed()->toArray())) {
            $team = "red";
        } else {
            $team = "blue";
        }

        //TODO : do announce check

        $announcesPoints = 0;

        foreach ($announces as $announce) {
            if ($announce[0] === 'Tierce') {
                $announcesPoints = $announcesPoints + 20;
            } elseif ($announce[0] === 'Cinquante') {
                $announcesPoints = $announcesPoints + 50;
            } elseif ($announce[0] === 'Cent') {
                $announcesPoints = $announcesPoints + 100;
            } elseif ($announce[0] === 'Carre') {
                $announcesPoints = $announcesPoints + 100;
            }
            $round->addAnnounceName($announce[0]);
        }

        if (in_array($player, $game->getTeamRed()->toArray())) {
            $round->setAnnounceRed($round->getAnnounceRed() + $announcesPoints);
        } else {
            $round->setAnnounceBlue($round->getAnnounceBlue() + $announcesPoints);
        }

        $round->setPlayerAnnounce($player);

        $roundManager->save($round);


        $update = new Update(
            'https://example.com/announce-'.$game->getCode(),
            json_encode($players[0]->getUsername())
        );

        $hub->publish($update);

        return new JsonResponse($players[1]->getUsername());

    }


    #[Route('/game/endGame/{code}', name: 'app_end_game')]
    public function endGamePage(string $code,RequestStack $requestStack, GameRepository $gameRepository,
                                UserRepository      $userRepository, HubInterface $hub, RoundManager $roundManager, GameManager $gameManager,
                                RoundSearchProvider $roundSearchProvider, CardRepository $cardRepository, UserManager $userManager)
    {
        $game = $gameRepository->findOneBy(['code' => $code]);

        if (!$game->isEnd()){
            return $this->redirectToRoute('app_party', ['code' => $code]);
        }

        return $this->render('game/endGame.html.twig', [
            'game' => $game
        ]);

    }

    #[Route('/game/endHeapOther/{code}', name: 'app_end_other_heap')]
    public function endHeapOther(string $code,RequestStack $requestStack, GameRepository $gameRepository,
                                 UserRepository      $userRepository, HubInterface $hub, RoundManager $roundManager, GameManager $gameManager,
                                 RoundSearchProvider $roundSearchProvider, CardRepository $cardRepository, UserManager $userManager)
    {

        $game = $gameRepository->findOneBy(['code' => $code]);
        $round = $game->getLastRound();
        $heap = $round->getHeap();
        $typeRound = $round->getType();
        $typeHeap = $heap[0]->getType();

        if ($typeHeap === $typeRound) {
            $strongestCard = $roundSearchProvider->strongestCardInHeapAsset($heap, $typeRound);
        } else {
            $strongestCard = $roundSearchProvider->strongestCardInHeapByType($heap, $typeRound, $typeHeap);
        }

        foreach ($game->getPlayers() as $player) {
            if (in_array($strongestCard, $player->getHand()->getCards()->toArray())) {
                $strongestPlayer = $player;
            }
        }
        return new JsonResponse($strongestPlayer->getUsername());

    }

    #[Route('/leftGame/{code}', name: 'app_left_game')]
    public function leftGame(string $code,RequestStack $requestStack, GameRepository $gameRepository,
                             UserRepository      $userRepository, HubInterface $hub, RoundManager $roundManager, GameManager $gameManager,
                             RoundSearchProvider $roundSearchProvider, CardRepository $cardRepository, UserManager $userManager)
    {

        $game = $gameRepository->findOneBy(['code' => $code]);

        $hub->publish(new Update(
            'https://example.com/leftGame-'.$game->getCode(),
            json_encode($this->getUser()->getUsername())
        ));

        return $this->redirectToRoute('app_home');
    }

    #[Route('/leftLobby/{code}', name: 'app_left_lobby')]
    public function leftLobby(string $code,Security $security, PartyRepository $partyRepository,
                              UserRepository      $userRepository, HubInterface $hub, PartyManager $partyManager, GameManager $gameManager,
                              RoundSearchProvider $roundSearchProvider, CardRepository $cardRepository, UserManager $userManager)
    {

        $lobby = $partyRepository->findOneBy(['code' => $code]);

        $lobby->removePlayer($userRepository->findOneBy(['username' => $security->getUser()->getUsername()]));

        $partyManager->save($lobby);

        $hub->publish(new Update(
            'https://example.com/NewPlayer',
        ));

        return new JsonResponse("ok");

    }


}
