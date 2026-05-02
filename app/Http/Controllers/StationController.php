<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Station;
use App\Models\User;
use App\Models\StationUser;
use App\Models\Brand;
use App\Models\Vote;
use App\Models\Gifts;
use App\Events\babyEvent;
use DB;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use App\Providers\RouteServiceProvider;
use App\Helpers\GlobalHelper;

class StationController extends Controller
{

    public function uploadBaby(Request $request)
    {
        $request->validate([
            'pledge_image' => 'required|image|max:2048', // max 2MB
            'pledge_text' => 'string|max:255',
            'charname' => 'string|max:255',
            'pledge_type' => 'required|string|in:text,coral',
        ]);

        $user = Auth::user();

        // Store the uploaded image in `public/babies`
        $path = $request->file('pledge_image')->store('public/babies');

        // Convert path to URL or relative path for saving
        $publicPath = Storage::url($path); // returns `/storage/babies/filename.gif`

        try {
            DB::beginTransaction();


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
            $stationUser->station_id = 4;
            $stationUser->time_spent = $secondsSpent;
            $stationUser->save();

            $user->pledge_image = $publicPath;
            $user->pledge_text = $request->pledge_text;
            if ($request->has('charname')) {
                $user->charname = $request->input('charname');
            }

            $user->save();
        // Fire the event
        broadcast(new babyEvent($pueblicPath, $user->pledge_text,$request->pledge_type,$user->charname))->toOthers();


            DB::commit();
            // Success response
            return response()->json(['message' => 'Station ID updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();

            // Handle the error, log it, or return an appropriate response
            return response()->json(['error' => $e], 500);
        }

        // Save to user


    }

    public function uploadBabyIpad(Request $request)
    {
        $request->validate([
            'pledge_image' => 'required|image|max:2048', // max 2MB
            'pledge_text' => 'string|max:255',
            'charname' => 'string|max:255',
            'pledge_type' => 'required|string|in:text,coral',
        ]);


        // Store the uploaded image in `public/babies`
        $path = $request->file('pledge_image')->store('public/babies');

        // Convert path to URL or relative path for saving
        $publicPath = Storage::url($path); // returns `/storage/babies/filename.gif`

        //check if image is uploaded
        if (!$publicPath) {
            return response()->json(['error' => 'Image upload failed.'], 500);
        }

        // Get charname from request or use default
        $charName = $request->charname ?? 'Baby';

        // Fire the event (use correct fields)
        broadcast(new babyEvent($publicPath, $request->pledge_text, $request->pledge_type, $charName))->toOthers();

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'Baby uploaded successfully',
            'imgPath' => $publicPath,
            'name' => $charName
        ]);
    }

    function workshopCongrats() {
        return view('workshopCongrats');
    }

    // public function index(Station $station)
    // {
    //     $user = StationUser::where('user_id', auth()->id())
    //         ->where('station_id', $station->id)
    //         ->exists();

    //     $choices = Station::with('answers', 'correctAnswer')
    //         ->where('id', $station->id)
    //         ->first();

    //     $gifts = \App\Models\Gifts::get();

    //      return view('station', compact('station', 'user', 'gifts','choices'));

    // }

    public function index(Station $station)
    {
        $user = StationUser::where('user_id', auth()->id())
            ->where('station_id', $station->id)
            ->exists();
        if ($station->id == 9 && $user == true) {
            return view('congrats');
        }

        if($station->id == 2 && $user == true) {
            return view('station', compact('station', 'user'));
        }

         return view('station', compact('station', 'user'));

    }


    public function extension(Station $station)
    {
        return view('extension');
    }

    public function congratsVote()
    {
        return view('congratsVote');
    }

    public function brand(Station $station)
    {
        $brands = Brand::get();
        return view('brand', compact('brands'));
    }
    public function puzzle(Station $station)
    {
        $user = User::with('stationUser')->where('id', auth()->id())->first();
        // dd($user->stationUser->count());

        $stationDone = $user->stationUser->count();
        $stations = Station::get();

        // Loop through each station and append a flag indicating if the user has it
        foreach ($stations as $station) {
            $userHasStation = $user
                ->StationUser()
                ->where('station_id', $station->id)
                ->exists();
            $station->status = $userHasStation;
        }

        $userId = Auth::id();

        $required = DB::table('stations')
            ->leftJoin('station_users', function ($join) use ($userId) {
                $join->on('stations.id', '=', 'station_users.station_id')->where('station_users.user_id', '=', $userId);
            })
            ->select('stations.id as station_id', 'stations.name as station_name', DB::raw('IF(station_users.station_id IS NULL, false, true) as is_gotten'))
            ->distinct()
            ->orderByRaw('is_gotten DESC')
            ->where('required', 1)
            ->get();
        $puzzleRequired = DB::table('stations')
            ->leftJoin('station_users', function ($join) use ($userId) {
                $join->on('stations.id', '=', 'station_users.station_id')->where('station_users.user_id', '=', $userId);
            })
            ->select('stations.id as station_id', 'stations.name as station_name', DB::raw('IF(station_users.station_id IS NULL, false, true) as is_gotten'))
            ->distinct()
            ->where('required', 1)
            ->orderBy('station_id', 'asc')
            ->get();
        // dd($required);
        $notRequired = DB::table('stations')
            ->leftJoin('station_users', function ($join) use ($userId) {
                $join->on('stations.id', '=', 'station_users.station_id')->where('station_users.user_id', '=', $userId);
            })
            ->select('stations.id as station_id', 'stations.name as station_name', DB::raw('IF(station_users.station_id IS NULL, false, true) as is_gotten'))
            ->where('stations.required', 0) // Prioritize is_gotten=true, then order by station_id
            ->orderByRaw('is_gotten DESC')
            ->limit(2)
            ->get();
        $puzzleNotRequired = DB::table('stations')
            ->leftJoin('station_users', function ($join) use ($userId) {
                $join->on('stations.id', '=', 'station_users.station_id')->where('station_users.user_id', '=', $userId);
            })
            ->select('stations.id as station_id', 'stations.name as station_name', DB::raw('IF(station_users.station_id IS NULL, false, true) as is_gotten'))
            ->where('stations.required', 0)
            ->orderByRaw('is_gotten DESC, station_id ASC') // Prioritize is_gotten=true, then order by station_id
            ->limit(2)
            ->get();

        $giftRequired = DB::table('stations')
            ->leftJoin('station_users', function ($join) use ($userId) {
                $join->on('stations.id', '=', 'station_users.station_id')->where('station_users.user_id', '=', $userId);
            })
            ->select('stations.id as station_id', 'stations.name as station_name', DB::raw('IF(station_users.station_id IS NULL, false, true) as is_gotten'))
            ->distinct()
            ->orderByRaw('is_gotten DESC')
            ->where('required', 1)
            ->having('is_gotten', true)
            ->get();
        $giftNotRequired = DB::table('stations')
            ->leftJoin('station_users', function ($join) use ($userId) {
                $join->on('stations.id', '=', 'station_users.station_id')->where('station_users.user_id', '=', $userId);
            })
            ->select('stations.id as station_id', 'stations.name as station_name', DB::raw('IF(station_users.station_id IS NULL, false, true) as is_gotten'))
            ->distinct()
            ->orderByRaw('is_gotten DESC')
            ->where('required', 0)
            ->limit(2)
            ->having('is_gotten', true)
            ->get();
        $claim = count($giftRequired) + count($giftNotRequired);

        $nurse = DB::table('stations')
            ->leftJoin('station_users', function ($join) use ($userId) {
                $join->on('stations.id', '=', 'station_users.station_id')->where('station_users.user_id', '=', $userId);
            })
            ->select('stations.id as station_id', 'stations.name as station_name', 'stations.nurse as station_nurse', DB::raw('IF(station_users.station_id IS NULL, false, true) as is_gotten'))
            ->distinct()
            ->orderBy('stations.id', 'asc')
            ->get();

        return view('puzzle', compact('stations', 'stationDone', 'required', 'notRequired', 'puzzleRequired', 'puzzleNotRequired', 'nurse', 'claim'));
    }

    public function castVote(Request $request)
    {
        $vote = new Vote();
        $vote->brand_id = $request->brand_id;
        $vote->save();

        return $vote;
    }

    public function brands()
    {
        $brands = DB::table('brands')->leftJoin('users', 'brands.id', '=', 'users.brand_id')->select('brands.id as brand_id', 'brands.name as brand_name', DB::raw('COUNT(users.id) as count'))->groupBy('brands.id', 'brands.name')->get();
        // dd($brands);
        return view('brands', compact('brands'));
    }

    public function vote()
    {
        $brands = Brand::get();
        // dd($brands);
        return view('vote', compact('brands'));
    }

    public function voteData()
    {
        $brands = DB::table('brands')->leftJoin('votes', 'brands.id', '=', 'votes.brand_id')->select('brands.id as brand_id', 'brands.name as brand_name', DB::raw('COUNT(votes.id) as count'))->groupBy('brands.id', 'brands.name')->get();
        //dd($brands);
        return view('votes', compact('brands'));
    }

    public function welcome()
    {
        $userId = Auth::id();

        $user = User::with('stationUser')->where('id', $userId)->first();

        $stationDone = $user->stationUser->count();
        $stations = Station::get();

        $completedStationIds = $user->stationUser->pluck('id')->toArray();

        // Add status flag to each station
        foreach ($stations as $station) {
            $station->status = $user->stationUser->contains('station_id', $station->id);
        }

        // Determine if stations 1-4 are all completed
        $canAccessStation4 = $stations->filter(fn($s) => $s->id <= 2)->every(fn($s) => $s->status == true);

        $isRedeemed = \App\Models\UserGift::where('user_id', $userId)
            ->where('is_redeemed', true)
            ->exists();

        $nextStation = $stations->firstWhere(function ($station) use ($user) {
            return !$user->stationUser()->where('station_id', $station->id)->exists();
        });

        if ($stationDone < 4) {
             return view('dashboard', compact('stations', 'stationDone', 'canAccessStation4', 'completedStationIds', 'nextStation','isRedeemed'));
        } else {
            return redirect()->route('congrats');
        }

    }

    public function pledgeDj()
    {
        // get details of station 4
        $station = Station::find(4);
        return view('pledgeDj', compact('station'));
    }

    public function scanner()
    {
        // dd('asdasd');
        return view('scanner');
    }


    public function scan(Request $request)
    {
        // Parse the URL to get the query string

        $qrCodeMessage = trim($request->qrCodeMessage);

        // Get the last character of the QR code message
       $station_id = substr($qrCodeMessage, -1);

        // dd($station_id);


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
            // if($stationUser)
            // {
            //     $userGift = new \App\Models\UserGift();
            //     $userGift->user_id = auth()->id();
            //     $userGift->is_redeemed = true;
            //     $userGift->save();
            // }

            DB::commit();
            // Success response
            return response()->json(['message' => 'Station ID updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();

            // Handle the error, log it, or return an appropriate response
            return response()->json(['error' => $e], 500);
        }
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




    public function admin()
    {
        $disk = Storage::disk('public');

        // Captures
        $captureFiles = collect($disk->files('captures'));
        $captureCount = $captureFiles->count();
        $captureSize  = $captureFiles->sum(fn($f) => $disk->size($f));
        $captureToday = $captureFiles->filter(fn($f) => Carbon::createFromTimestamp($disk->lastModified($f))->isToday())->count();

        // Videos
        $videoFiles = collect($disk->files('videos'));
        $videoCount = $videoFiles->count();
        $videoSize  = $videoFiles->sum(fn($f) => $disk->size($f));
        $videoToday = $videoFiles->filter(fn($f) => Carbon::createFromTimestamp($disk->lastModified($f))->isToday())->count();

        // Uploads per day (last 14 days) — captures + videos combined
        $allFiles = $captureFiles->merge($videoFiles);
        $uploadsPerDay = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $uploadsPerDay[$date] = $allFiles->filter(function ($f) use ($disk, $date) {
                return Carbon::createFromTimestamp($disk->lastModified($f))->toDateString() === $date;
            })->count();
        }

        // Recent uploads (last 8, mixed)
        $recentUploads = $allFiles->map(function ($f) use ($disk) {
            $isVideo = str_starts_with($f, 'videos/');
            return [
                'filename'    => basename($f),
                'url'         => $disk->url($f),
                'type'        => $isVideo ? 'video' : 'capture',
                'size'        => $disk->size($f),
                'uploaded_at' => Carbon::createFromTimestamp($disk->lastModified($f)),
            ];
        })->sortByDesc('uploaded_at')->take(8)->values();

        $data = [
            'capture_count'   => $captureCount,
            'capture_size'    => $captureSize,
            'capture_today'   => $captureToday,
            'video_count'     => $videoCount,
            'video_size'      => $videoSize,
            'video_today'     => $videoToday,
            'total_size'      => $captureSize + $videoSize,
            'uploads_per_day' => $uploadsPerDay,
            'recent_uploads'  => $recentUploads,
        ];

        return view('dashboardadmin', compact('data'));
    }

    public function users()
    {
        $today = Carbon::today();
        $permission = auth()->user()->getPermissionNames()->first();

        $startDate = Carbon::create(2025, 6, 17);
        $data['users'] = User::whereDate('created_at', '>=', $startDate->toDateString())->whereDoesntHave('roles', function ($q) {
            $q->where('name', 'admin');
        })->with('stationUser')->orderBy('id', 'desc')->get();

        $data['usersCount'] = User::whereDate('created_at', '>=', $startDate->toDateString())->whereDoesntHave('roles', function ($q) {
            $q->where('name', 'admin');
        })->count();
        $data['userToday'] = User::whereDate('created_at', $today)->whereDoesntHave('roles', function ($q) {
            $q->where('name', 'admin');
        })->count();

        $usersWithSixStationUsers = User::whereDate('created_at', '>=', $startDate->toDateString())->has('stationUser', '>=', 1)->count();
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

        return view('users', compact('data', 'permission'));
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

        return view('userData', compact('user', 'totalMinutes', 'permission'));
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

    public function verify(Request $request)
    {
        $otp = implode('', $request->input('otp'));
        // dd(auth()->user());
        if ($otp == auth()->user()->otp) {

            // Success: Clear session OTP
            Session::forget(['otp', 'otp_sent_at']);
            $user= auth()->user();
            $user->otp_verified = 1;
            $user->email_verified_at = Carbon::now();
            $user->save();

            // $data = GlobalHelper::createSampleProfile();
            //  dd($data);

            // return redirect(RouteServiceProvider::HOME);
            return redirect()->route('register.welcome');
        }

        return back()->withErrors(['otp' => 'Invalid OTP']);
    }

    public function resend(Request $request)
    {
        $user = auth()->user();

        $otp = rand(100000, 999999);

         GlobalHelper::sendOtpSms($user->number, $otp);

        $user->otp = $otp;
        $user->save();


        return response()->json([
            'success' => true,
            'message' => 'OTP resent successfully.'
        ]);
    }

    public function getValue()
    {

        // Count all image files in storage/app/public/babies
        $imageCount = collect(\Storage::files('public/babies'))
            ->filter(function($file) {
                return preg_match('/\\.(jpg|jpeg|png|gif|webp)$/i', $file);
            })
            ->count();

            $imageCount = $imageCount + 3000;
        return response()->json(['count' => $imageCount]);
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

    public function giftSelection(Request $request)
    {
        $userId = auth()->id();

    $isRedeemed = \App\Models\UserGift::where('user_id', $userId)
        ->where('is_redeemed', true)
        ->exists();

        return view('giftSelection',compact('isRedeemed'));
    }

    public function stamping(Station $station)
    {
        $user = StationUser::where('user_id', auth()->id())
            ->where('station_id', $station->id)
            ->exists();

        $choices = Station::with('answers', 'correctAnswer')
            ->where('id', $station->id)
            ->first();

        $gifts = \App\Models\Gifts::get();

         return view('stamping', compact('station', 'user', 'gifts','choices'));

    }


    public function discover()
    {
        return view('discover');
    }

    public function userGifts()
    {
        try {
            $userGifts = \App\Models\UserGift::with(['user', 'gift'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return view('admin.user-gifts', compact('userGifts'));
        } catch (\Exception $e) {
            return redirect()->route('admin')->with('error', 'Error loading user gifts: ' . $e->getMessage());
        }
    }

    public function mediaLibrary()
    {
        $captureDisk = Storage::disk('public');
        $captures = collect($captureDisk->files('captures'))->map(function ($file) use ($captureDisk) {
            return [
                'filename'  => basename($file),
                'path'      => $file,
                'url'       => Storage::disk('public')->url($file),
                'size'      => $captureDisk->size($file),
                'uploaded_at' => \Carbon\Carbon::createFromTimestamp($captureDisk->lastModified($file)),
                'type'      => 'capture',
            ];
        })->sortByDesc('uploaded_at')->values();

        $videos = collect($captureDisk->files('videos'))->map(function ($file) use ($captureDisk) {
            return [
                'filename'  => basename($file),
                'path'      => $file,
                'url'       => Storage::disk('public')->url($file),
                'size'      => $captureDisk->size($file),
                'uploaded_at' => \Carbon\Carbon::createFromTimestamp($captureDisk->lastModified($file)),
                'type'      => 'video',
            ];
        })->sortByDesc('uploaded_at')->values();

        $stats = [
            'total_captures' => $captures->count(),
            'total_videos'   => $videos->count(),
            'captures_size'  => $captures->sum('size'),
            'videos_size'    => $videos->sum('size'),
        ];

        return view('admin.media', compact('captures', 'videos', 'stats'));
    }

    public function deleteMedia(string $type, string $filename)
    {
        // Sanitise — only allow safe filenames (no path traversal)
        if (!in_array($type, ['capture', 'video']) || !preg_match('/^[\w\-\.]+$/', $filename)) {
            return response()->json(['error' => 'invalid_request'], 422);
        }

        $folder = $type === 'capture' ? 'captures' : 'videos';
        $path   = $folder . '/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['error' => 'not_found'], 404);
        }

        Storage::disk('public')->delete($path);

        return response()->json(['success' => true]);
    }

    public function adminGifts()
    {
        // Debug: Test if method is being called
        logger('adminGifts method called');

        try {
            $gifts = \App\Models\Gifts::withCount('userGifts')
                ->orderBy('created_at', 'desc')
                ->get();

            $totalGifts = $gifts->count();
            $enabledGifts = $gifts->where('enabled', true)->count();
            $disabledGifts = $gifts->where('enabled', false)->count();
            $totalSelectedGifts = \App\Models\UserGift::count();

            $stats = [
                'total_gifts' => $totalGifts,
                'enabled_gifts' => $enabledGifts,
                'disabled_gifts' => $disabledGifts,
                'total_selected' => $totalSelectedGifts
            ];

            return view('admin.gifts', compact('gifts', 'stats'));
        } catch (\Exception $e) {
            logger('Error in adminGifts: ' . $e->getMessage());
            return response('Error: ' . $e->getMessage(), 500);
        }
    }

    public function toggleGift(\App\Models\Gifts $gift)
    {
        $gift->enabled = !$gift->enabled;
        $gift->save();

        $status = $gift->enabled ? 'enabled' : 'disabled';
        return redirect()->back()->with('success', "Gift '{$gift->name}' has been {$status} successfully.");
    }

    public function stamp(Request $request)
    {
       
        // Get the last character of the QR code message
        $station_id = $request->station;


        // Assume that `$station_id` is validated before this point

        try {
            DB::beginTransaction();

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
            // if ($station_id == 3 && $request->has('selected_gift_id') && $request->selected_gift_id) {
            //     $userGift = new \App\Models\UserGift();
            //     $userGift->user_id = auth()->id();
            //     $userGift->gift_id = $request->selected_gift_id;
            //     $userGift->station_id = $station_id;
            //     $userGift->is_redeemed = false;
            //     $userGift->save();
            // }

            DB::commit();
            // Success response
            return response()->json(['message' => 'Station ID updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();

            // Handle the error, log it, or return an appropriate response
            return response()->json(['error' => $e], 500);
        }
    }

    public function redeemGift(Request $request)
    {

        $userId = auth()->id();

        // Check if already redeemed
        $existingGift = \App\Models\UserGift::where('user_id', $userId)
            ->where('is_redeemed', true)
            ->first();

        if ($existingGift) {
            return response()->json([
                'success' => false,
                'message' => 'You have already redeemed your gift.',
            ], 200);
        }

        // Create new redeemed gift record
        $userGift = new \App\Models\UserGift();
        $userGift->user_id = $userId;
        $userGift->is_redeemed = true;
        $userGift->save();

        return response()->json([
            'success' => true,
            'message' => 'Gift redeemed successfully! ',
            'redirect' => route('congrats'),
        ], 200);
    }

    public function checkExisting(Request $request)
    {
        $code = $request->code;
        $check = User::where('number', $code)->exists();
        return $check;
    }

    public function redemption(Request $request)
    {

        $userId = Auth::id();

        $user = User::with('stationUser')->where('id', $userId)->first();

        $stationDone = $user->stationUser->count();
        $stations = Station::get();

        $completedStationIds = $user->stationUser->pluck('id')->toArray();

        // Add status flag to each station
        foreach ($stations as $station) {
            $station->status = $user->stationUser->contains('station_id', $station->id);
        }


        $isRedeemed = \App\Models\UserGift::where('user_id', $userId)
            ->where('is_redeemed', true)
            ->exists();

        if ($stationDone < 1) {
             return view('redemption', compact('stations', 'stationDone', 'completedStationIds','isRedeemed'));
        } else {
            return redirect()->route('congrats');
        }
        // return view('redemption');
    }

    public function giftRedeem()
    {

    }

}



