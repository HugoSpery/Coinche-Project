<?php

namespace App\Service\Party;

use App\Exception\InvalidCodeException;
use App\Repository\PartyRepository;
use http\Exception\InvalidArgumentException;
use PHPUnit\Util\Exception;

class PartyFormHandler
{

    public function __construct(private PartyManager $partyManager, private PartyRepository $partyRepository)
    {
    }

    public function handleFindPartyForm($form,$request)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && !$form->isValid()) {
            throw new \Exception($form->getErrors(true)->current()->getMessage());
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->get('data')->getData();
            if ($data === null || $data === '' || strlen($data) !== 8) {
                throw new InvalidCodeException("Le code doit contenir 8 caractères");
            }
            $party = $this->partyRepository->findOneBy(['code' => $data]);
            if ($party === null) {
                throw new InvalidArgumentException("Aucune partie trouvée avec ce code");
            }
            return $party;
        }
        return null;
    }
}