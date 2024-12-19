<?php

namespace App\Twig\Components;

use App\Repository\FriendRequestRepository;
use App\Twig\Components\Trait\KnpPaginationTrait;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/friend_request.html.twig')]
final class FriendRequestList
{
    use DefaultActionTrait;
    use KnpPaginationTrait;



    public function __construct(private FriendRequestRepository $friendRequestRepository,private PaginatorInterface $paginator,private Security $security)
    {

    }

    #[LiveAction]
    public function getFriendRequestList()
    {
        $requestFriendsUser = $this->friendRequestRepository->getUsernameSenderRequest($this->security->getUser(),$this->query);
        $requests = $this->paginator->paginate(
            $requestFriendsUser,
            $this->page,
            9,
            ['pageParameterName' => 'page_requests'] // Nom unique pour cette pagination
        );
        return $requests;
    }
}
