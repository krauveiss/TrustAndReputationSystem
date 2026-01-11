<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reputation extends Model
{
    protected $fillable = [
        'user_id',
        'score',
        'level'
    ];


    protected function casts()
    {
        return [
            'score' => 'integer',
            'level' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime'
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
