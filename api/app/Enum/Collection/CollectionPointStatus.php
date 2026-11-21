<?php

namespace App\Enum\Collection;

enum CollectionPointStatus: string
{
    case RECEIVING_MATERIALS = 'Recebendo materiais';
    case TEMPORARILY_UNAVAILABLE = 'Temporariamente indisponível.';
}
