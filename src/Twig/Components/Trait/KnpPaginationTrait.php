<?php

namespace App\Twig\Components\Trait;

use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

trait KnpPaginationTrait
{
    #[LiveProp]
    public int $page = 1;

    #[LiveAction]
    public function setPage(#[LiveArg] int $page) : void{
        $this->page = $page;
    }

    #[LiveProp(writable: true)]
    public string $query = '';

}