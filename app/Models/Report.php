<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['reporter_id', 'target_user_id', 'reason', 'status', 'moderator_id'];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function moderatorUser()
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }


    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
