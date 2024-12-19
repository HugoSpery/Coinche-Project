<?php

namespace App\Enum;

enum Type : string
{
    case CLUBS = 'clubs';
    case DIAMONDS = 'diamonds';
    case HEARTS = 'hearts';
    case SPADES = 'spades';
}