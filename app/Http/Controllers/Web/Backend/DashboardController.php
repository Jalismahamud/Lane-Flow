<?php

namespace App\Http\Controllers\Web\Backend;


use App\Models\User;
use App\Models\Review;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;


class DashboardController extends Controller
{
    public function index()
    {

        try {
            $totalUsers     = User::where('role', 'user')->count();
        
            return view('backend.layouts.dashboard',
                [
                    'totalUsers'     => $totalUsers,
                ]
            );
        } catch (\Exception $e) {

            Log::info($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

}
