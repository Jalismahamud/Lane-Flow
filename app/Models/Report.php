<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'user_id',
        'text',
        'audio',
        'status',
        'lane',
        'latitude',
        'longitude',
        'reported_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

