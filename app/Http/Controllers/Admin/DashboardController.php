<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Station;
use App\Models\StationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

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
            'registrationsPerHour' => $this->getRegistrationHourlyChartData(),
        ];
        

        return view('admin.dashboard', compact('stats', 'chartData'));
    }

    public function scanner()
    {
        return view('admin.scanner');
    }

    public function users()
    {
        $today = Carbon::today();
        $permission = auth()->user()->getPermissionNames()->first();

        $startDate = Carbon::create(2025, 6, 17);
        $data['users'] = User::whereDate('created_at', '>=', $startDate->toDateString())->with('stationUser')->orderBy('id', 'desc')->get();

        $data['usersCount'] = User::whereDate('created_at', '>=', $startDate->toDateString())->count();
        $data['userToday'] = User::whereDate('created_at', $today)->count();

        $usersWithSixStationUsers = User::whereDate('created_at', '>=', $startDate->toDateString())->has('stationUser', '>=', 5)->count();
        $data['completedUsers'] = $usersWithSixStationUsers;

        if ($data['usersCount'] > 0) {
            $data['percentage'] = number_format(($usersWithSixStationUsers / $data['usersCount']) * 100, 2);
        } else {
            $data['percentage'] = 0; // Avoid division by zero
        }

        $averageTimespentByStation = StationUser::select('station_id', \DB::raw('AVG(time_spent) as average_timespent'))->groupBy('station_id')->get()->keyBy('station_id');

        $stations = Station::pluck('name', 'id');

        foreach ($data['users'] as $user) {
            $userStations = $user->stationUser->pluck('station_id')->toArray();
            $user->stations = $stations->map(function ($name, $id) use ($userStations, $averageTimespentByStation) {
                return [
                    'name' => $name,
                    'value' => in_array($id, $userStations),
                ];
            });
        }

        $data['stations'] = $stations->map(function ($name, $id) use ($averageTimespentByStation) {
            return [
                'name' => $name,
                'average_timespent' => number_format(($averageTimespentByStation->get($id)['average_timespent'] ?? 0) / 60, 2),
            ];
        });

        return view('admin.users', compact('data', 'permission'));
    }

    public function userData(User $user)
    {
        $averagePlaytimeByUser = StationUser::where('user_id', $user->id)->avg('time_spent');
        $permission = auth()->user()->getPermissionNames()->first();

        $stations = Station::pluck('name', 'id');

        $averageTimespentByStation = StationUser::where('user_id', $user->id)
            ->orderBy('id', 'asc')
            ->get();
        $total = StationUser::where('user_id', $user->id)
            ->orderBy('id', 'asc')
            ->sum('time_spent');
        $totalMinutes = $total / 60;
        $totalMinutes = number_format($totalMinutes, 2);

        $userStations = $user->stationUser->pluck('station_id')->toArray();
        $numStations = count($userStations);

        $user->stations = $stations->map(function ($name, $id) use ($userStations, $user) {
            $spent = StationUser::where('user_id', $user->id)
                ->where('station_id', $id)
                ->first();
            if (!$spent) {
                $minute = 0;
            } else {
                $seconds = $spent->time_spent;
                $minute = $seconds / 60;
                $minute = number_format($minute, 2);
            }
            return [
                'name' => $name,
                'value' => in_array($id, $userStations),
                'time_spent' => $minute,
                'id' => $id,
            ];
        });

        return view('admin.userData', compact('user', 'totalMinutes', 'permission'));
    }

    public function userDelete($id)
    {
        $user = User::findOrFail($id);
        
        // Check if user is protected admin
        if ($user->isProtectedAdmin()) {
            return redirect()->back()->with('error', 'This admin user is protected and cannot be deleted.');
        }

        // Delete related station user entries
        $user->stationUser()->delete(); // ✅ Correct for hasMany

        // Delete the user
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    public function verifyAdmin(Request $request)
    {
        $otp = $request->input('otp');
        $userId = $request->input('user_id'); // Get user ID from the request

        $user = User::find($userId); // Find the user by ID

        if (!$user) {
            return back()->withErrors(['user' => 'User not found']);
        }

        if ($otp == $user->otp) {
            // Success: Clear session OTP
            Session::forget(['otp', 'otp_sent_at']);
            $user->otp_verified = 1;
            $user->email_verified_at = Carbon::now();
            $user->save();

            //  $data = GlobalHelper::createSampleProfile();
              return back()->with('success', 'OTP verified successfully!');
        }

        return back()->withErrors(['otp' => 'Invalid OTP']);
    }

    public function editUser(Request $request)
    {
        $user = User::find($request->id);

        if ($user) {
            $user->email = $request->email;
            $user->save();

            return response()->json(['success' => true, 'message' => 'User email updated successfully']);
        }

        return response()->json(['success' => false, 'message' => 'User not found'], 404);
    }

    public function check(Request $request)
    {
        $check = StationUser::where('user_id', $request->user_id)
            ->where('station_id', $request->station_id)
            ->first();

        if (!$check) {
            $stationUser = new StationUser();
            $stationUser->user_id = $request->user_id;
            $stationUser->station_id = $request->station_id;
            $stationUser->time_spent = 60;
            $stationUser->save();
        } else {
            $check->delete();
        }

        return $check;
    }


    public function scan(Request $request)
    {
        // Parse the URL to get the query string

        $qrCodeMessage = trim($request->qrCodeMessage);

        // Get the last character of the QR code message
        $station_id = substr($qrCodeMessage, -1);


        // Assume that `$station_id` is validated before this point

        try {
            DB::beginTransaction();

            if ($station_id != $request->station) {
                return response()->json(['message' => 'Invalid Qr', 'status' => 'error'], 400);
            }

            $lastStation = StationUser::where('user_id', auth()->id())->orderBy('id', 'desc')->first();

            if (empty($lastStation)) {
                $lastLoginTime = Auth::user()->last_login_at;
                $currentDateTime = Carbon::now();
                $timeSpent = $currentDateTime->diff($lastLoginTime);
                $minutesSpent = $timeSpent->i; // Minutes spent
                $secondsDifference = $timeSpent->s; // Seconds

                // Convert minutes to seconds
                $secondsSpent = $minutesSpent * 60 + $secondsDifference;
            } else {
                $lastLoginTime = $lastStation->created_at;
                $currentDateTime = Carbon::now();
                $timeSpent = $currentDateTime->diff($lastLoginTime);
                $minutesSpent = $timeSpent->i; // Minutes spent
                $secondsDifference = $timeSpent->s; // Seconds
                // Convert minutes to seconds
                $secondsSpent = $minutesSpent * 60 + $secondsDifference;
            }

            $stationUser = new StationUser();
            $stationUser->user_id = auth()->id();
            $stationUser->station_id = $station_id;
            $stationUser->time_spent = $secondsSpent;
            $stationUser->save();

            // Handle gift selection for station 3
            if ($station_id == 3 && $request->has('selected_gift_id') && $request->selected_gift_id) {
                $userGift = new \App\Models\UserGift();
                $userGift->user_id = auth()->id();
                $userGift->gift_id = $request->selected_gift_id;
                $userGift->station_id = $station_id;
                $userGift->is_redeemed = false;
                $userGift->save();
            }

            DB::commit();
            // Success response
            return response()->json(['message' => 'Station ID updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();

            // Handle the error, log it, or return an appropriate response
            return response()->json(['error' => $e], 500);
        }
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
    
    private function getRegistrationHourlyChartData():array
    {
        $startDate = Carbon::create(2025, 11, 28);

        $registrationsPerHour = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('LOWER(DATE_FORMAT(created_at, "%l%p")) as hour'),
            DB::raw('COUNT(*) as registrations')
        )
        ->whereNotNull('created_at')
        ->whereDate('created_at', '>=', $startDate->toDateString())
        ->groupBy('date', 'hour')
        ->havingRaw('hour IS NOT NULL AND hour <> \'\'')
        ->get()
        ->groupBy('hour');

        $hours = $registrationsPerHour->keys()
        ->map(function ($hour) {
            return \Carbon\Carbon::createFromFormat('gA', strtoupper($hour))->format('gA');
        })
        ->sort()
        ->values()
    ->toArray();

        $dates = $registrationsPerHour->flatten()->pluck('date')->unique()->sort()->values()->toArray();

        // Sort hours chronologically
$sortedHours = $registrationsPerHour->keys()
    ->sortBy(function ($hour) {
        return \Carbon\Carbon::createFromFormat('gA', strtoupper($hour))->hour;
    })
    ->values();

// Format hours for display (e.g., 10am, 11am)
$hours = $sortedHours->map(function ($hour) {
    return \Carbon\Carbon::createFromFormat('gA', strtoupper($hour))->format('ga');
})->toArray();

// Prepare series data
$series = [];

foreach ($dates as $date) {
    $dataPerHour = [];

    foreach ($sortedHours as $hour) {
        // Get the registration count for this date & hour, default 0
        $row = $registrationsPerHour[$hour]->firstWhere('date', $date);
        $dataPerHour[] = $row->registrations ?? 0;
    }

    $series[] = [
        'name' => $date,
        'data' => $dataPerHour
    ];
}

    return[
        'dates'  => $dates,
        'hours'  => $hours,
        'series' => $series,
    ];

        // dd($registrationsPerHour->toArray());

    }

}
