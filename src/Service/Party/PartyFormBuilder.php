<?php

namespace App\Service\Party;

use App\Entity\Lobby;
use App\Form\FindPartyFormType;
use Symfony\Component\Form\FormFactoryInterface;

class PartyFormBuilder
{
    public function __construct(private FormFactoryInterface $formFactory)
    {
    }

    public function buildFindPartyForm()
    {
        return $this->formFactory->create(FindPartyFormType::class,new Lobby());
    }

}