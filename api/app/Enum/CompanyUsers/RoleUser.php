<?php

namespace App\Enum\CompanyUsers;

enum RoleUser: string
{
    case RESPONSIBLE = 'responsible';
    case ADMIN = 'admin';
    case MEMBER = 'member';
}
