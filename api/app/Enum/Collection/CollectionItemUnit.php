<?php

namespace App\Enum\Collection;

enum CollectionItemUnit: string
{
    case UNIT = 'UN';
    case QUANTITY = 'QDE';
    case LITRE = 'L';
    case MILLILITRE = 'ML';
    case KILOGRAM = 'KG';
    case GRAM = 'G';
    case TONNE = 'T';
    case CUBIC_METRE = 'M3';
}
