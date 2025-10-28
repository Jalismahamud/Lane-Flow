<?php

namespace App\Http\Controllers\Api\Backend;

use Exception;
use App\Models\UserSearchHistory;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class ApiUserSearchHistoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();

            $histories = UserSearchHistory::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->success($histories, 'Search history retrieved successfully.', 200);
        } catch (Exception $e) {
            Log::error('Search History Retrieval Error: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'search_query' => ['required', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                return $this->error([], $validator->errors()->first(), 422);
            }

            $user = auth('api')->user();
            $query = $request->search_query;

            $latitude = null;
            $longitude = null;
            $location_name = $query;

            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'LaneFlow/1.0 (+https://laneflow.app)'
                ])->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1
                ]);

                if ($response->ok() && count($response->json()) > 0) {
                    $data = $response->json()[0];
                    $latitude = $data['lat'] ?? null;
                    $longitude = $data['lon'] ?? null;
                    $location_name = $data['display_name'] ?? $query;
                }
            } catch (Exception $geoError) {
                Log::warning('Geocoding API failed: ' . $geoError->getMessage());
            }

            $search = UserSearchHistory::create([
                'user_id'       => $user->id,
                'search_query'  => $query,
                'location_name' => $location_name,
                'latitude'      => $latitude,
                'longitude'     => $longitude,
            ]);

            return $this->success($search, 'Search saved successfully.', 201);
        } catch (Exception $e) {
            Log::error('Search Creation Error: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = auth('api')->user();

            $history = UserSearchHistory::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$history) {
                return $this->error([], 'Search history not found.', 404);
            }

            $history->delete();

            return $this->success([], 'Search history deleted successfully.', 200);
        } catch (Exception $e) {

            Log::error('Search History Delete Error: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function clearAll(Request $request)
    {
        try {
            $user = auth('api')->user();

            UserSearchHistory::where('user_id', $user->id)->delete();

            return $this->success([], 'All search history cleared successfully.', 200);
        } catch (Exception $e) {
            Log::error('Clear All Search History Error: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
