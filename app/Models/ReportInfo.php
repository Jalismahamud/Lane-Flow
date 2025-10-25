<?php
// app/Models/ReportInfo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'path',
        'mime',
        'duration_seconds',
        'file_size'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'file_size' => 'integer',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function getUrlAttribute()
    {
        return route('reports.audio.stream', ['report' => $this->report_id, 'info' => $this->id]);
    }
}
