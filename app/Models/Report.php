<?php


namespace App\Models;

use App\Models\ReportInfo;
use App\Models\ReportStatusChange;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'official_cleared', 'admin_note'];

    protected $casts = [
        'official_cleared' => 'boolean',
        'expires_at' => 'datetime',
        'clear_report_count' => 'integer',
        'blocked_report_count' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];


    public const TYPE_BLOCKED_LANE = 'blocked_lane';
    public const TYPE_EMERGENCY_CLEAR = 'emergency_lane_clearance';
    public const TYPE_TEMP_SHIFT = 'temporary_lane_shift';

    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_CLEARED = 'cleared';
    public const STATUS_REMOVED = 'removed';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function infos()
    {
        return $this->hasMany(ReportInfo::class);
    }

    public function statusChanges()
    {
        return $this->hasMany(ReportStatusChange::class);
    }

 
    public function scopeWithinMiles($query, float $lat, float $lng, int $miles = 5)
    {
        $haversine = "(3959 * acos(
            cos(radians(?)) *
            cos(radians(latitude)) *
            cos(radians(longitude) - radians(?)) +
            sin(radians(?)) *
            sin(radians(latitude))
        ))";

        return $query
            ->selectRaw("reports.*, {$haversine} AS distance", [$lat, $lng, $lat])
            ->having('distance', '<=', $miles)
            ->orderBy('distance');
    }

    public function scopeActive($query)
    {
        return $query
            ->whereIn('status', [self::STATUS_BLOCKED])
            ->where(function ($sub) {
                $sub->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }
        return $query;
    }
}
