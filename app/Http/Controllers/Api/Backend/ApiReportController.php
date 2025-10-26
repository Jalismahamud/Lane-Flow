<?php

namespace App\Http\Controllers\Api\Backend;

use Exception;
use App\Models\Report;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class ApiReportController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'distance' => ['nullable', 'numeric', 'min:0', 'max:100']
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            $user = auth('api')->user();
            $query = Report::with('user:id,name,avatar');

            if ($request->filled(['latitude', 'longitude'])) {
                $latitude = $request->latitude;
                $longitude = $request->longitude;
                $distance = $request->distance ?? 5;

                $query->selectRaw("
                    *,
                    (
                        6371 * acos(
                            cos(radians(?)) * cos(radians(latitude)) *
                            cos(radians(longitude) - radians(?)) +
                            sin(radians(?)) * sin(radians(latitude))
                        )
                    ) AS distance", [$latitude, $longitude, $latitude])
                    ->having('distance', '<=', $distance)
                    ->orderBy('distance');
            }

            $reports = $query->orderBy('created_at', 'desc')->get();

            $reports = $reports->map(function ($report) {
                return [
                    'id' => $report->id,
                    'text' => $report->text,
                    'audio' => $report->audio ? url($report->audio) : null,
                    'status' => $report->status,
                    'lane' => $report->lane,
                    'latitude' => $report->latitude,
                    'longitude' => $report->longitude,
                    'reported_at' => Carbon::parse($report->reported_at)->format('Y-m-d H:i:s'),
                    'user' => $report->user,
                ];
            });

            return $this->success($reports, 'Reports retrieved successfully', 200);
        } catch (Exception $e) {
            Log::error('Reports Retrieval Error: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'text'              => ['nullable', 'string'],
                'audio'             => ['nullable', 'file', 'mimes:mp3,wav,m4a', 'max:10240'],
                'status'            => ['required', 'string', 'in:blocked,clear,accident,other'],
                'lane'              => ['nullable', 'string', 'in:left,middle,right,none'],
                'latitude'          => ['required', 'numeric', 'between:-90,90'],
                'longitude'         => ['required', 'numeric', 'between:-180,180'],
                'reported_at'       => ['nullable', 'date'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            $data = $validator->validated();
            $user = auth('api')->user();

            if ($request->hasFile('audio')) {
                $audioPath = Helper::uploadImage($request->file('audio'), 'reports/audio');
                $data['audio'] = $audioPath;
            }

            $data['user_id'] = $user->id;
            $report = Report::create($data);

            $report['audio'] = url($report->audio);

            return $this->success($report, 'Report created successfully.', 201);
        } catch (Exception $e) {

            Log::error('Report Creation Error: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'text' => ['nullable', 'string'],
                'audio' => ['nullable', 'file', 'mimes:mp3,wav,m4a', 'max:10240'],
                'status' => ['nullable', 'string', 'in:blocked,clear,accident,other'],
                'lane' => ['nullable', 'string', 'in:left,middle,right,none'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'reported_at' => ['nullable', 'date'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            $user = auth('api')->user();
            $report = Report::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$report) {
                return $this->error([], 'Report not found.', 404);
            }

            $data = $validator->validated();

            if ($request->hasFile('audio')) {
                if ($report->audio) {
                    Helper::deleteImage($report->audio);
                }
                $audioPath = Helper::uploadImage($request->file('audio'), 'reports/audio');
                $data['audio'] = $audioPath;
            }

            $report['reported_at'] = now();

            $report->update($data);

            $report['audio'] = url($report->audio);

            return $this->success($report, 'Report updated successfully.', 200);
        } catch (Exception $e) {

            Log::error('Report Update Error: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = auth('api')->user();
            $report = Report::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$report) {
                return $this->error([], 'Report not found.', 404);
            }

            if ($report->audio) {
                Helper::deleteImage($report->audio);
            }

            $report->delete();

            return $this->success([], 'Report deleted successfully.', 200);
        } catch (Exception $e) {

            Log::error('Report Delete Error: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function changeToggleReportStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => ['required', 'in:clear,blocked,accident,other'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            $report = Report::find($id);

            if (!$report) {
                return $this->error([], 'Report not found.', 404);
            }

            $report->status = $request->status;
            $report->save();

            return $this->success(['status' => $report->status], 'Report status updated successfully.', 200);
        } catch (Exception $e) {

            Log::error('Report Status Update Error: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
