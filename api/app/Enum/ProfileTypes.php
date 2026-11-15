<?php

namespace App\Enum;

enum ProfileTypes: string
{
    case COMMON = 'common';
    case COLLECTOR = 'collector';
    case COMPANY = 'company';
}
