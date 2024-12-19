<?php

namespace App\Twig\Components;

use App\Repository\FriendRequestRepository;
use App\Repository\PartyRequestRepository;
use App\Repository\TeamRequestRepository;
use App\Service\User\UserSearchProvider;
use App\Twig\Components\Trait\KnpPaginationTrait;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/friend_list.html.twig')]
final class FriendList
{
    use DefaultActionTrait;
    use KnpPaginationTrait;

    public function __construct(private FriendRequestRepository $friendRequestRepository,private UserSearchProvider $userSearchProvider,private PaginatorInterface $paginator,private Security $security,private PartyRequestRepository $partyRequestRepository,private TeamRequestRepository $teamRequestRepository)
    {

    }

    #[LiveAction]
    public function getFriendList()
    {
        $data = $this->userSearchProvider->getFriendList($this->security->getUser(),$this->query);
        $friends = $this->paginator->paginate(
            $data,
            $this->page,
            9,
            ['pageParameterName' => 'page_friends'] // Nom unique pour cette pagination
        );
        return $friends;
    }

    #[LiveAction]
    public function getPartyRequest()
    {

        return $this->partyRequestRepository->getUsernameReceiverRequest([$this->security->getUser()]);
    }

    #[LiveAction]
    public function getTeamRequestSender(){
        return $this->teamRequestRepository->getUsernameSenderTeamRequest($this->security->getUser());
    }

    #[LiveAction]
    public function getTeamRequestReceiver(){
        return $this->teamRequestRepository->getUsernameReceiverTeamRequest($this->security->getUser());
    }


    #[LiveAction]
    public function getInviteRequest()
    {
        return $this->partyRequestRepository->getUsernameSenderRequest([$this->security->getUser()]);
    }
}
