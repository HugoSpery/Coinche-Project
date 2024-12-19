<?php

namespace App\Twig\Components;

use App\Repository\FriendRequestRepository;
use App\Repository\UserRepository;
use App\Service\User\UserSearchProvider;
use App\Twig\Components\Trait\KnpPaginationTrait;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/user_list.html.twig')]
final class UserList
{
    use DefaultActionTrait;
    use KnpPaginationTrait;

    public function __construct(private FriendRequestRepository $friendRequestRepository,private UserSearchProvider $userSearchProvider,private UserRepository $userRepository,private PaginatorInterface $paginator,private Security $security)
    {

    }

    #[LiveAction]
    public function getUserList()
    {
        $users = $this->paginator->paginate(
            $this->userRepository->findUserSearch($this->query),
            $this->page,
            5,
        );
        return $users;
    }

    #[LiveAction]
    public function getRequestAlreadyDone()
    {
        return $this->friendRequestRepository->getUsernameReceiverRequest([$this->security->getUser()]);

    }
}
