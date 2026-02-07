<?php

namespace App\Services;


use App\Models\AdminLog;
use App\Models\User;

class LogService
{
    function log($user_id, $executive, $action, $comment = '')
    {
        AdminLog::create([
            'executive_id' => $executive->id,
            'action' => $action,
            'user_id' => $user_id,
            'comment' => $comment
        ]);
    }
}
