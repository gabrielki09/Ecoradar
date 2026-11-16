<?php

namespace App\Enum\CollectionRequest;

enum CollectionRequestStatus: string
{
    case OPEN = 'open';
    case ACCEPTED = 'accepted';
    case COLLECTED = 'collected';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
