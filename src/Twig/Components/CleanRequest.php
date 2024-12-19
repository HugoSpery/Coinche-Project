<?php

namespace App\Twig\Components;

use App\Repository\FriendRequestRepository;
use App\Repository\PartyRequestRepository;
use App\Repository\TeamRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: 'components/clean_request.html.twig')]
final class CleanRequest
{
    use DefaultActionTrait;

    public function __construct(private TeamRequestRepository $teamRequestRepository,private PartyRequestRepository $partyRequestRepository)
    {
    }

    /**
     * Nettoie la base de données.
     */
    #[LiveAction]
    public function clean(): void
    {
        // Exemple de nettoyage : suppression des entrées obsolètes
        $this->teamRequestRepository->deleteExpiredTeamRequest();
        $this->partyRequestRepository->deleteExpiredRequest();

    }
}
