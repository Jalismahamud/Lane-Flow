<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Log;

class ReportStatisticsController extends Controller
{
    public function index(Request $request)
    {

        try {
            // Get filter parameters
            $filterType = $request->query('filter_type', 'monthly');
            $month = $request->query('month', Carbon::now()->month);
            $year = $request->query('year', Carbon::now()->year);
            $week = $request->query('week', Carbon::now()->weekOfYear);
            $search = $request->query('search', '');
            $status = $request->query('status', '');
            $lane = $request->query('lane', '');

            // Calculate date range based on filter type
            switch ($filterType) {
                case 'daily':
                    $start = Carbon::createFromDate((int)$year, (int)$month, 1)->startOfDay();
                    $end = (clone $start)->endOfMonth()->endOfDay();
                    break;
                case 'weekly':
                    $start = Carbon::now()->setISODate((int)$year, (int)$week)->startOfWeek();
                    $end = (clone $start)->endOfWeek();
                    break;
                case 'monthly':
                default:
                    $start = Carbon::createFromDate((int)$year, 1, 1)->startOfDay();
                    $end = (clone $start)->endOfYear()->endOfDay();
                    break;
            }

            // Total reports
            $totalReports = Report::count();

            // Reports in selected period
            $reportsInPeriod = Report::with('user')
                ->whereBetween('created_at', [$start, $end])
                ->orderBy('created_at', 'desc')
                ->get();

            // Prepare chart data based on filter type
            $createdData = [];
            $updatedData = [];

            if ($filterType === 'daily') {
                $period = CarbonPeriod::create($start->toDateString(), $end->toDateString());
                foreach ($period as $dt) {
                    $label = $dt->format('Y-m-d');
                    $createdData[$label] = 0;
                    $updatedData[$label] = 0;
                }
            } elseif ($filterType === 'weekly') {
                for ($i = 0; $i < 7; $i++) {
                    $day = (clone $start)->addDays($i);
                    $label = $day->format('Y-m-d');
                    $createdData[$label] = 0;
                    $updatedData[$label] = 0;
                }
            } else { // monthly
                for ($m = 1; $m <= 12; $m++) {
                    $label = Carbon::create((int)$year, $m, 1)->format('M Y');
                    $createdData[$label] = 0;
                    $updatedData[$label] = 0;
                }
            }

            // Fill data
            foreach ($reportsInPeriod as $r) {
                if ($filterType === 'monthly') {
                    $createdLabel = Carbon::parse($r->created_at)->format('M Y');
                } else {
                    $createdLabel = Carbon::parse($r->created_at)->format('Y-m-d');
                }

                if (isset($createdData[$createdLabel])) {
                    $createdData[$createdLabel]++;
                }

                if ($r->updated_at) {
                    if ($filterType === 'monthly') {
                        $updatedLabel = Carbon::parse($r->updated_at)->format('M Y');
                    } else {
                        $updatedLabel = Carbon::parse($r->updated_at)->format('Y-m-d');
                    }

                    if (isset($updatedData[$updatedLabel])) {
                        $updatedData[$updatedLabel]++;
                    }
                }
            }

            // Recent reports with pagination and search
            $recentReportsQuery = Report::with('user');

            if ($search) {
                $recentReportsQuery->where(function($q) use ($search) {
                    $q->where('text', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%")
                      ->orWhere('lane', 'like', "%{$search}%")
                      ->orWhereHas('user', function($query) use ($search) {
                          $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            if ($status) {
                $recentReportsQuery->where('status', $status);
            }

            if ($lane) {
                $recentReportsQuery->where('lane', $lane);
            }

            $recentReports = $recentReportsQuery->orderBy('created_at', 'desc')->paginate(15);

            // Get all reports with coordinates for map
            $mapReports = Report::with('user')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('created_at', 'desc')
                ->get();

            // Calculate zone statistics based on geographical clustering
            $zoneData = $this->calculateZoneStatistics($mapReports);


            // Get unique statuses and lanes for filters (only from existing data)
            $statuses = Report::distinct()
                ->whereNotNull('status')
                ->pluck('status')
                ->filter()
                ->sort()
                ->values();

            // Statistics
            $totalThisMonth = Report::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();


            $updatesThisMonth = Report::whereMonth('updated_at', Carbon::now()->month)
                ->whereYear('updated_at', Carbon::now()->year)
                ->where('created_at', '!=', 'updated_at')
                ->count();

            // Status distribution for pie chart
            $statusDistribution = Report::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();


            return view('backend.layouts.report_statistics.index', [
                'totalReports' => $totalReports,
                'totalThisMonth' => $totalThisMonth,
                'updatesThisMonth' => $updatesThisMonth,
                'createdData' => $createdData,
                'createdDataJson' => json_encode($createdData),
                'updatedData' => $updatedData,
                'updatedDataJson' => json_encode($updatedData),
                'selectedMonth' => (int)$month,
                'selectedYear' => (int)$year,
                'selectedWeek' => (int)$week,
                'filterType' => $filterType,
                'search' => $search,
                'selectedStatus' => $status,
                'selectedLane' => $lane,
                'recentReports' => $recentReports,
                'mapReports' => $mapReports,
                'zoneData' => $zoneData,
                'statusDistribution' => $statusDistribution,
                'statuses' => $statuses,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Calculate zone statistics based on geographical clustering
     * Groups reports into zones based on proximity
     */
    private function calculateZoneStatistics($reports)
    {
        $zones = [];
        $clusterRadius = 0.05; // Approximately 5km radius for clustering

        foreach ($reports as $report) {
            if (!$report->latitude || !$report->longitude) {
                continue;
            }

            $lat = (float) $report->latitude;
            $lng = (float) $report->longitude;
            $assigned = false;

            // Try to assign to existing zone
            foreach ($zones as $key => &$zone) {
                $distance = $this->calculateDistance(
                    $lat, $lng,
                    $zone['center_lat'], $zone['center_lng']
                );

                if ($distance <= $clusterRadius) {
                    $zone['count']++;
                    $zone['reports'][] = $report;
                    $assigned = true;
                    break;
                }
            }

            // Create new zone if not assigned
            if (!$assigned) {
                $zoneName = $this->getZoneName($lat, $lng);
                $zones[$zoneName] = [
                    'name' => $zoneName,
                    'center_lat' => $lat,
                    'center_lng' => $lng,
                    'count' => 1,
                    'reports' => [$report]
                ];
            }
        }

        // Sort zones by count
        usort($zones, function($a, $b) {
            return $b['count'] - $a['count'];
        });

        return $zones;
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Generate zone name based on coordinates
     */
    private function getZoneName($lat, $lng)
    {
        // Define major zones for Dhaka and surrounding areas
        // You can customize this based on your actual geographical areas

        if ($lat >= 23.75 && $lat <= 23.82 && $lng >= 90.35 && $lng <= 90.42) {
            return 'Central Dhaka';
        } elseif ($lat >= 23.70 && $lat <= 23.75 && $lng >= 90.35 && $lng <= 90.42) {
            return 'Old Dhaka';
        } elseif ($lat >= 23.75 && $lat <= 23.85 && $lng >= 90.42 && $lng <= 90.50) {
            return 'Gulshan-Banani';
        } elseif ($lat >= 23.82 && $lat <= 23.90 && $lng >= 90.35 && $lng <= 90.45) {
            return 'Uttara';
        } elseif ($lat >= 23.70 && $lat <= 23.78 && $lng >= 90.30 && $lng <= 90.38) {
            return 'Mirpur-Mohammadpur';
        } elseif ($lat >= 23.72 && $lat <= 23.80 && $lng >= 90.42 && $lng <= 90.50) {
            return 'Motijheel-Khilgaon';
        } else {
            return 'Other Areas';
        }
    }
}
