<?php
// app/Services/ReportService.php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportInfo;
use App\Models\ReportStatusChange;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ReportService
{
    public function createReport(array $data, $user, $audioFile)
    {
        return DB::transaction(function () use ($data, $user, $audioFile) {
            try {
                $expiresAt = $this->calculateExpiry(
                    $data['type'],
                    $data['duration_minutes'] ?? null
                );

                $report = Report::create([
                    'user_id' => $user->id,
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'type' => $data['type'],
                    'lane' => $data['lane'],
                    'description' => $data['description'] ?? null,
                    'duration_minutes' => $data['duration_minutes'] ?? null,
                    'status' => Report::STATUS_BLOCKED,
                    'expires_at' => $expiresAt,
                    'blocked_report_count' => 1,
                    'clear_report_count' => 0,
                ]);

                if ($audioFile) {
                    $this->saveAudio($report, $audioFile);
                }

                $this->logStatusChange(
                    $report,
                    null,
                    Report::STATUS_BLOCKED,
                    'Initial report',
                    $user->id
                );

                Log::info('Report created successfully', [
                    'report_id' => $report->id,
                    'user_id' => $user->id,
                    'type' => $report->type,
                ]);

                return $report->load('infos', 'user:id,name');
            } catch (\Exception $e) {
                Log::error('Report creation failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        });
    }

    public function updateReport(Report $report, array $data, $user)
    {
        if ($report->user_id !== $user->id && !$user->isAdmin()) {
            throw new \Exception('Unauthorized');
        }

        return DB::transaction(function () use ($report, $data) {
            $report->update([
                'description' => $data['description'] ?? $report->description,
                'lane' => $data['lane'] ?? $report->lane,
            ]);

            Log::info('Report updated', ['report_id' => $report->id]);

            return $report->fresh();
        });
    }

    public function deleteReport(Report $report, $user)
    {
        if ($report->user_id !== $user->id && !$user->isAdmin()) {
            throw new \Exception('Unauthorized');
        }

        return DB::transaction(function () use ($report, $user) {
            try {
                // Load infos before deletion for file cleanup
                $infos = $report->infos()->get();
                $fromStatus = $report->status;

                // Perform soft delete
                $report->status = Report::STATUS_REMOVED;
                $report->save();
                $report->delete();

                // Log status change
                $this->logStatusChange(
                    $report,
                    $fromStatus,
                    Report::STATUS_REMOVED,
                    'Deleted by user',
                    $user->id
                );

                // Delete associated files
                foreach ($infos as $info) {
                    if ($info->path && Storage::disk('local')->exists($info->path)) {
                        Storage::disk('local')->delete($info->path);
                    }
                }

                Log::info('Report deleted', [
                    'report_id' => $report->id,
                    'user_id' => $user->id,
                    'from_status' => $fromStatus
                ]);

                return true;
            } catch (\Exception $e) {
                Log::error('Failed to delete report', [
                    'report_id' => $report->id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        });
    }

    public function toggleClearStatus(Report $report, string $action, $user, ?string $note = null)
    {
        return DB::transaction(function () use ($report, $action, $user, $note) {
            $lockedReport = Report::lockForUpdate()->find($report->id);

            $fromStatus = $lockedReport->status;

            if ($action === 'clear') {
                $lockedReport->increment('clear_report_count');
                $toStatus = Report::STATUS_CLEARED;
            } else {
                $lockedReport->increment('blocked_report_count');
                $toStatus = Report::STATUS_BLOCKED;
            }

            $this->logStatusChange($lockedReport, $fromStatus, $toStatus, $note, $user->id);

            $this->evaluateReportStatus($lockedReport);

            Log::info('Report status toggled', [
                'report_id' => $lockedReport->id,
                'action' => $action,
                'user_id' => $user->id,
            ]);

            return $lockedReport->fresh(['infos', 'user:id,name']);
        });
    }

    public function adminUpdateReport(Report $report, array $data, $user)
    {
        return DB::transaction(function () use ($report, $data, $user) {
            $fromStatus = $report->status;
            $toStatus = $data['to_status'];

            $report->status = $toStatus;

            if (!empty($data['official_clear'])) {
                $report->official_cleared = true;
            }

            if ($toStatus === Report::STATUS_CLEARED) {
                $report->expires_at = now()->addMinutes(5);
            } elseif ($toStatus === Report::STATUS_REMOVED) {
                $report->expires_at = now()->addMinute();
            }

            $report->admin_note = $data['note'] ?? null;
            $report->save();

            $this->logStatusChange(
                $report,
                $fromStatus,
                $toStatus,
                $data['note'] ?? null,
                $user->id,
                (bool)($data['official_clear'] ?? false)
            );

            Log::info('Admin updated report', [
                'report_id' => $report->id,
                'admin_id' => $user->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
            ]);

            return $report->fresh();
        });
    }

    protected function calculateExpiry(string $type, ?int $durationMinutes)
    {
        if ($durationMinutes) {
            return now()->addMinutes($durationMinutes);
        }

        return match ($type) {
            Report::TYPE_EMERGENCY_CLEAR => now()->addMinutes(30),
            Report::TYPE_TEMP_SHIFT => now()->addHours(3),
            default => now()->addMinutes(30),
        };
    }

    // protected function saveAudio(Report $report, $file): void
    // {
    //     $path = $file->store('reports/audio', 'local');

    //     $duration = null;
    //     if (class_exists('\getID3')) {
    //         try {
    //             $getID3 = new \getID3();
    //             $fileInfo = $getID3->analyze(storage_path('app/' . $path));
    //             $duration = $fileInfo['playtime_seconds'] ?? null;
    //         } catch (\Exception $e) {
    //             Log::warning('Could not extract audio duration', [
    //                 'report_id' => $report->id,
    //                 'error' => $e->getMessage()
    //             ]);
    //         }
    //     }

    //     ReportInfo::create([
    //         'report_id' => $report->id,
    //         'path' => $path,
    //         'mime' => $file->getMimeType(),
    //         'duration_seconds' => $duration,
    //         'file_size' => $file->getSize()
    //     ]);
    // }

    protected function saveAudio(Report $report, $file): void
    {
        if (!$file || !$file->isValid()) {
            throw new \Exception('Invalid or missing audio file.');
        }

        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();
        $originalExtension = $file->getClientOriginalExtension();


        $destinationPath = public_path('uploads/audio');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $fileName = uniqid() . '_' . time() . '.' . $originalExtension;
        $relativePath = 'uploads/audio/' . $fileName;

        $file->move($destinationPath, $fileName);


        $duration = null;
        if (class_exists('\getID3')) {
            try {
                $getID3 = new \getID3();
                $fileInfo = $getID3->analyze(public_path($relativePath));
                $duration = $fileInfo['playtime_seconds'] ?? null;
            } catch (\Exception $e) {
                Log::warning('Could not extract audio duration', [
                    'report_id' => $report->id,
                    'error' => $e->getMessage()
                ]);
            }
        }


        ReportInfo::create([
            'report_id' => $report->id,
            'path' => $relativePath,
            'mime' => $mimeType,
            'duration_seconds' => $duration,
            'file_size' => $fileSize
        ]);
    }




    protected function logStatusChange(
        Report $report,
        $fromStatus,
        $toStatus,
        $note,
        $userId,
        $isOfficial = false
    ): void {
        ReportStatusChange::create([
            'report_id' => $report->id,
            'user_id' => $userId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $note,
            'is_official' => $isOfficial,
        ]);
    }

    protected function evaluateReportStatus(Report $report): void
    {
        $shouldClear = ($report->clear_report_count >= 5) || $report->official_cleared;

        if ($shouldClear && $report->status !== Report::STATUS_CLEARED) {
            $report->status = Report::STATUS_CLEARED;
            $report->expires_at = now()->addMinutes(5);
            $report->save();

            Log::info('Report auto-cleared', [
                'report_id' => $report->id,
                'clear_count' => $report->clear_report_count,
                'official_cleared' => $report->official_cleared,
            ]);
        }
    }

    public function checkDuplicateReport($userId, float $lat, float $lng): bool
    {
        $haversine = "(3959 * acos(
            cos(radians(?)) *
            cos(radians(latitude)) *
            cos(radians(longitude) - radians(?)) +
            sin(radians(?)) *
            sin(radians(latitude))
        ))";

        $duplicate = Report::where('user_id', $userId)
            ->where('created_at', '>', now()->subHour())
            ->selectRaw("reports.*, {$haversine} AS distance", [$lat, $lng, $lat])
            ->having('distance', '<', 0.062)
            ->first();

        return $duplicate !== null;
    }

    public function expireReports()
    {
        $now = now();
        $expired = Report::whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->get();

        $count = 0;

        foreach ($expired as $report) {
            try {
                DB::transaction(function () use ($report) {
                    $fromStatus = $report->status;

                    $report->status = Report::STATUS_REMOVED;
                    $report->save();

                    $note = $fromStatus === Report::STATUS_CLEARED
                        ? 'Auto-removed after grace period'
                        : 'Auto-removed on expiration';

                    $this->logStatusChange(
                        $report,
                        $fromStatus,
                        Report::STATUS_REMOVED,
                        $note,
                        null
                    );
                });

                $count++;
            } catch (\Exception $e) {
                Log::error('Failed to expire report', [
                    'report_id' => $report->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("Expired {$count} reports");

        return $count;
    }
}
