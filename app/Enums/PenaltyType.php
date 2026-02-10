<?php

namespace App\Enums;

enum PenaltyType: string
{
    case TEMP_BLOCK = 'temporary_block';
    case PERM_BLOCK = 'permanent_block';
    case UNTIMEOUT = 'untimeout';
    case UNBAN = 'unban';
}
