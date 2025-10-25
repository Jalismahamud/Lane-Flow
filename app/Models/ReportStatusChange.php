<?php
// app/Models/ReportStatusChange.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportStatusChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'user_id',
        'from_status',
        'to_status',
        'note',
        'is_official'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_official' => 'boolean',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
