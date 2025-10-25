<?php

namespace App\Http\Controllers\Api\Backend;

use App\Models\Report;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Services\ReportService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\ToggleClearRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Http\Requests\AdminUpdateReportRequest;

class ApiReportController extends Controller
{
    use ApiResponse;

    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'integer', 'min:1', 'max:50'],
            'include_cleared' => ['nullable', 'boolean'],
            'type' => ['nullable', Rule::in([
                Report::TYPE_BLOCKED_LANE,
                Report::TYPE_EMERGENCY_CLEAR,
                Report::TYPE_TEMP_SHIFT
            ])],
            'status' => ['nullable', Rule::in([
                Report::STATUS_BLOCKED,
                Report::STATUS_CLEARED
            ])],
        ]);

        $lat = (float) $validated['lat'];
        $lng = (float) $validated['lng'];
        $radius = (int) ($validated['radius'] ?? 5);
        $includeCleared = (bool) ($validated['include_cleared'] ?? false);

        $cacheKey = "reports:{$lat}:{$lng}:{$radius}:{$includeCleared}:" .
            ($validated['type'] ?? 'all') . ':' .
            ($validated['status'] ?? 'all');

        $reports = Cache::remember($cacheKey, 10, function () use ($validated, $lat, $lng, $radius, $includeCleared) {
            $query = Report::query();

            if (!$includeCleared) {
                $query->active();
            }

            if (!empty($validated['type'])) {
                $query->byType($validated['type']);
            }

            if (!empty($validated['status'])) {
                $query->byStatus($validated['status']);
            }

            return $query->withinMiles($lat, $lng, $radius)
                ->with(['infos', 'user:id,name,avatar'])
                ->get();
        });

        return $this->success($reports, 'Reports retrieved successfully',200);
    }

    public function store(StoreReportRequest $request)
    {
        $user = $request->user();
        $lastReport = Report::where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        if ($lastReport && $lastReport->created_at->diffInSeconds(now()) < 5) {
            return $this->error('You can only create one report per minute', 429);
        }

        // Check duplicate location
        $isDuplicate = $this->reportService->checkDuplicateReport(
            $user->id,
            $request->latitude,
            $request->longitude
        );

        if ($isDuplicate) {
            return $this->error('You have already reported at this location (within 1 hour)', 429);
        }

        try {
            $report = $this->reportService->createReport(
                $request->validated(),
                $user,
                $request->file('audio')
            );

            // Clear cache
            $this->clearReportCache($report->latitude, $report->longitude);

            return $this->success($report, 'Report created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create report. Please try again', 500);
        }
    }

    public function show(Report $report)
    {
        $report->load(['infos', 'statusChanges.user:id,name', 'user:id,name']);
        return $this->success($report, 'Report details retrieved successfully');
    }

    public function update(UpdateReportRequest $request, Report $report)
    {
        try {
            $updatedReport = $this->reportService->updateReport(
                $report,
                $request->validated(),
                $request->user()
            );

            return $this->success($updatedReport, 'Report updated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage() === 'Unauthorized'
                ? 'You are not authorized to update this report'
                : 'Failed to update report', 403);
        }
    }

    public function destroy($reportId)
    {
        try {
            $report = Report::where('user_id', Auth::id())->find($reportId);
            if (!$report) {
                return $this->error([], 'Report not found', 404);
            } else if ($report->user_id !== Auth::id()) {
                return $this->error([], 'You are not authorized to delete this report', 403);
            }

            try {
                if ($report->infos) {
                    foreach ($report->infos as $info) {
                        $filePath = storage_path('app/' . $info->path);
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                }
                $report->delete();
            } catch (\Exception $e) {
                return $this->error([], 'Failed to delete report', 500);
            }

            $this->clearReportCache($report->latitude, $report->longitude);
            return $this->success(null, 'Report deleted successfully');
        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function toggleClear(ToggleClearRequest $request, $reportId)
    {

        $report = Report::find($reportId);

        if (!$report) {
            return $this->error([],'Report not found', 404);
        }
        try {
            $updatedReport = $this->reportService->toggleClearStatus(
                $report,
                $request->action,
                $request->user(),
                $request->note
            );

            $this->clearReportCache($report->latitude, $report->longitude);

            $message = $request->action === 'clear'
                ? 'Report marked as cleared'
                : 'Report marked as blocked';

            return $this->success($updatedReport, $message);
        } catch (\Exception $e) {
            return $this->error('Failed to update status', 500);
        }
    }

    public function adminUpdate(AdminUpdateReportRequest $request, Report $report)
    {
        try {
            $updatedReport = $this->reportService->adminUpdateReport(
                $report,
                $request->validated(),
                $request->user()
            );

            $this->clearReportCache($report->latitude, $report->longitude);

            return $this->success($updatedReport, 'Admin update successful');
        } catch (\Exception $e) {
            return $this->error('Failed to update', 500);
        }
    }

    public function myReports(Request $request)
    {
        $reports = $request->user()
            ->reports()
            ->with('infos', 'statusChanges.user:id,name')
            ->latest()
            ->get();

        return $this->success($reports, 'Your report list');
    }

    public function streamAudio(Report $report, $infoId)
    {
        $info = $report->infos()->findOrFail($infoId);
        $path = storage_path('app/' . $info->path);

        if (!file_exists($path)) {
            abort(404, 'Audio file not found');
        }

        return response()->file($path, [
            'Content-Type' => $info->mime ?? 'audio/mpeg',
            'Content-Disposition' => 'inline',
        ]);
    }


    protected function clearReportCache($lat, $lng)
    {
        $radii = [5, 10, 20, 50];
        $includeOptions = [true, false];
        $types = ['all', Report::TYPE_BLOCKED_LANE, Report::TYPE_EMERGENCY_CLEAR, Report::TYPE_TEMP_SHIFT];
        $statuses = ['all', Report::STATUS_BLOCKED, Report::STATUS_CLEARED];

        foreach ($radii as $radius) {
            foreach ($includeOptions as $include) {
                foreach ($types as $type) {
                    foreach ($statuses as $status) {
                        $cacheKey = "reports:{$lat}:{$lng}:{$radius}:{$include}:{$type}:{$status}";
                        Cache::forget($cacheKey);
                    }
                }
            }
        }
    }
}
