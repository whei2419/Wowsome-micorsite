<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $stats = [
            'totalCustomers' => $this->getTotalCustomers(),
            'todayCustomers' => $this->getTodayCustomers(),
            'completionRate' => $this->getCompletionRate(),
            'customersFinished' => $this->getCustomersFinished(),
        ];

        $chartData = [
            'registrations' => $this->getRegistrationChartData(),
        ];

        return view('admin.dashboard', compact('stats', 'chartData'));
    }

    /**
     * Get total customers count
     */
    private function getTotalCustomers(): int
    {
        return User::count();
    }

    /**
     * Get today's customers count
     */
    private function getTodayCustomers(): int
    {
        return User::whereDate('created_at', today())->count();
    }

    /**
     * Get completion rate percentage
     */
    private function getCompletionRate(): float
    {
        $total = User::count();
        if ($total === 0) return 0;
        
        $completed = User::whereHas('stationUser', function($query) {
            $query->whereIn('station_id', [1, 2]);
        })
        ->withCount(['stationUser' => function($query) {
            $query->whereIn('station_id', [1, 2]);
        }])
        ->get()
        ->filter(function($user) {
            return $user->station_user_count >= 2;
        })
        ->count();

        return round(($completed / $total) * 100, 2);
    }

    /**
     * Get customers finished count
     */
    private function getCustomersFinished(): int
    {
        return User::whereHas('stationUser', function($query) {
            $query->whereIn('station_id', [1, 2]);
        })
        ->withCount(['stationUser' => function($query) {
            $query->whereIn('station_id', [1, 2]);
        }])
        ->get()
        ->filter(function($user) {
            return $user->station_user_count >= 2;
        })
        ->count();
    }

    /**
     * Get registration chart data for the last 30 days
     */
    private function getRegistrationChartData(): array
    {
        $registrations = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();

        return [
            'dates' => $registrations->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('M d');
            })->toArray(),
            'counts' => $registrations->pluck('count')->toArray(),
        ];
    }
}
