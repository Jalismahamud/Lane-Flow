<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {

            $totalUsers   = User::where('role', 'user')->count();
            $totalReviews = 0;
            $totalRatings = 0;
            $growth       = 0;

            $chartData = User::query()
                ->where('role', 'user')
                ->selectRaw('DATE(created_at) AS join_date, COUNT(*) AS count')
                ->groupBy('join_date')
                ->orderBy('join_date', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'join_date' => $item->join_date,
                        'count'     => (int) $item->count,
                        'timestamp' => strtotime($item->join_date . ' 00:00:00') * 1000,
                    ];
                })
                ->values()
                ->toArray();

            $users = User::where('role', 'user')
                ->select('id', 'name', 'avatar', 'created_at')
                ->get()
                ->map(function ($user) {
                    $joinDate = $user->created_at->format('Y-m-d');

                    if ($user->avatar) {
                        if (filter_var($user->avatar, FILTER_VALIDATE_URL)) {
                            $avatarUrl = $user->avatar;
                        } else {
                            $avatarUrl = asset($user->avatar);

                        }
                    } else {
                        $avatarUrl = asset('default/default-avatar.png');
                    }

                    return [
                        'id'         => $user->id,
                        'name'       => $user->name,
                        'avatar'     => $avatarUrl,
                        'join_date'  => $joinDate,
                    ];
                })
                ->toArray();

            return view('backend.layouts.dashboard', compact(
                'totalUsers',
                'totalReviews',
                'totalRatings',
                'growth',
                'chartData',
                'users'
            ));

        } catch (\Exception $e) {

            Log::error('Dashboard Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load dashboard.');
        }
    }
}
