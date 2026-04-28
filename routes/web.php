<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\IpadController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/concierge', function () {
    return redirect('/concierge/login');
});

Route::get('/', function () {
    return redirect()->route('login');
})->name('welcome');

Route::get('/start', function () {
    return view('start');
})->middleware('auth')->name('start');

Route::get('/gallery', function (\Illuminate\Http\Request $request) {
    return view('gallery', ['showBack' => $request->query('from') === 'player']);
})->middleware('auth')->name('gallery');

Route::get('/gallery/printer', function (\Illuminate\Http\Request $request) {
    return view('gallery', ['mode' => 'printer', 'showBack' => $request->query('from') === 'player']);
})->middleware('auth')->name('gallery.printer');

Route::get('/gallery/items', function (\Illuminate\Http\Request $request) {
    $disk    = \Illuminate\Support\Facades\Storage::disk('public');
    $perPage = 12;
    $type    = $request->query('type'); // 'photos' | 'videos' | null (initial load)
    $page    = max(1, (int) $request->query('page', 1));

    $paginate = function (array $files, string $downloadPrefix) use ($disk, $perPage, $page) {
        usort($files, fn($a, $b) => $disk->lastModified($b) <=> $disk->lastModified($a));
        $total  = count($files);
        $slice  = array_slice($files, ($page - 1) * $perPage, $perPage);
        $items  = array_values(array_map(fn($path) => [
            'url'      => $disk->url($path),
            'download' => url($downloadPrefix . rawurlencode($path)),
        ], $slice));
        return ['items' => $items, 'total' => $total, 'has_more' => ($page * $perPage) < $total];
    };

    if ($type === 'photos') {
        $result = $paginate($disk->files('captures'), '/captures/download?file=');
        return response()->json(['photos' => $result['items'], 'photos_total' => $result['total'], 'photos_has_more' => $result['has_more'], 'page' => $page, 'per_page' => $perPage]);
    }

    if ($type === 'videos') {
        $result = $paginate($disk->files('videos'), '/videos/download?file=');
        return response()->json(['videos' => $result['items'], 'videos_total' => $result['total'], 'videos_has_more' => $result['has_more'], 'page' => $page, 'per_page' => $perPage]);
    }

    // Initial load — return first page of both
    $photos = $paginate($disk->files('captures'), '/captures/download?file=');
    $videos = $paginate($disk->files('videos'), '/videos/download?file=');

    return response()->json([
        'photos'           => $photos['items'],
        'photos_total'     => $photos['total'],
        'photos_has_more'  => $photos['has_more'],
        'videos'           => $videos['items'],
        'videos_total'     => $videos['total'],
        'videos_has_more'  => $videos['has_more'],
        'per_page'         => $perPage,
    ]);
})->middleware('auth')->name('gallery.items');

// Public download for scanned QR codes — no auth so phones can access it
Route::get('/captures/download', function (\Illuminate\Http\Request $request) {
    $file = $request->query('file', '');
    // Security: only allow files inside the captures/ folder
    if (!preg_match('#^captures/[^/]+\.(jpg|jpeg|png|gif|webp)$#i', $file)) {
        abort(404);
    }
    if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($file)) {
        abort(404);
    }
    return \Illuminate\Support\Facades\Storage::disk('public')->download($file);
})->name('captures.download');

// Public video download for scanned QR codes
Route::get('/videos/download', function (\Illuminate\Http\Request $request) {
    $file = $request->query('file', '');
    // Security: only allow files inside the videos/ folder
    if (!preg_match('#^videos/[^/]+\.(mp4|mov|webm|mkv|avi|mts|m2ts|wmv)$#i', $file)) {
        abort(404);
    }
    if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($file)) {
        abort(404);
    }
    return \Illuminate\Support\Facades\Storage::disk('public')->download($file);
})->name('videos.download');

// Publisher simulator (testing only — remove in production)
Route::get('/publisher-sim', function () {
    return view('publisher-sim');
})->middleware('auth')->name('publisher.sim');

Route::get('/player', function () {
    return view('player');
})->middleware('auth')->name('player');

// Public — called by Windows app (no session/auth required)
Route::get('/player/callback', [IpadController::class, 'playerCallback'])->name('player.callback');

Route::get('/player/ping',     [IpadController::class, 'playerPing'])->middleware('auth')->name('player.ping');
Route::post('/player/play',    [IpadController::class, 'playerPlay'])->middleware('auth')->name('player.play');
Route::post('/player/pause',   [IpadController::class, 'playerPause'])->middleware('auth')->name('player.pause');
Route::post('/player/resume',  [IpadController::class, 'playerResume'])->middleware('auth')->name('player.resume');
Route::post('/player/restart', [IpadController::class, 'playerRestart'])->middleware('auth')->name('player.restart');
Route::post('/player/done',    [IpadController::class, 'playerDone'])->middleware('auth')->name('player.done');



Route::get('/upload-baby', function () {
    return view('upload-baby');
})->name('upload.baby.form');

Route::post('/uploadBabyIpad', 'App\Http\Controllers\StationController@uploadBabyIpad')->name('upload.babyIpad');

Route::post('/pushCoral', [IpadController::class, 'pushCoral'])->name('ipad.pushCoral');

Route::get('/listen-baby', function () {
    return view('listen-baby');
})->name('listen.baby.form');

Route::get('/listen-babyV2', function () {
    return view('liveFeedVip');
})->name('liveFeedVip');



Route::get('/pad', function () {
    return view('error');
});


Route::get('/admin/login', action: function () {
    return view('auth.admin-login');
});

Route::get('/concierge/login', action: function () {
    return view('auth.concierge-login');
});

Route::get('/ipad', [IpadController::class, 'index'])->name('ipad.index');
Route::get('/ipad-2', [IpadController::class, 'index2'])->name('ipad.index2');

Route::get('/counter-value', 'App\Http\Controllers\StationController@getValue')->name('pledge.counter');
Route::get('/ipad-pledge-info',action: function(){
        return view('ipad.info');
    })->name('ipad.info');

Route::get('/ipad-pledge-info-2',function(){
        return view('ipad.info2');
    })->name('ipad.info2');


Route::get('/ipad-select-message-type',function(){
        return view('ipad.message-type');
    })->name('ipad.message.type');

Route::get('/ipad-select-message-type-duplicate',function(){
        return view('ipad.message-type-duplicate');
    })->name('ipad.message.type.duplicate');

Route::get('/congrats', function () {
    return view('congrats');
})->name('congrats');

Route::get('/voteyourfav', function () {
    return view('welcomeVote');
})->name('welcomeVote');
Route::get('/vote', 'App\Http\Controllers\StationController@vote')->name('vote');
Route::post('/castVote', 'App\Http\Controllers\StationController@castVote')->name('castVote');
Route::get('/voteData', 'App\Http\Controllers\StationController@voteData')->name('voteData');
Route::get('/congratsVote', 'App\Http\Controllers\StationController@congratsVote')->name('congratsVote');

Route::group(['middleware' => ['admin']], function () {
    Route::get('/admin', 'App\Http\Controllers\StationController@admin')->name('admin');
    Route::get('/admin/users', 'App\Http\Controllers\StationController@users')->name('users');
    Route::get('/admin/scanner', 'App\Http\Controllers\StationController@scanner')->name('scanner');

    Route::post('verify-otp-admin', 'App\Http\Controllers\StationController@verifyAdmin')->name('verifyAdmin');

    Route::post('/workshop/scan', 'App\Http\Controllers\WorkshopController@scan')->name('workshop.scan');

    Route::post('/admin/logout', 'App\Http\Controllers\LoginController@destroy')->name('admin.logout');

    // Admin Gifts Management Routes
    Route::get('/admin/gifts', 'App\Http\Controllers\StationController@adminGifts')->name('admin.gifts');
    Route::post('/admin/gifts/{gift}/toggle', 'App\Http\Controllers\StationController@toggleGift')->name('admin.gifts.toggle');
    Route::get('/admin/user-gifts', 'App\Http\Controllers\StationController@userGifts')->name('admin.user.gifts');

    Route::get('/admin/bookings', [BookingController::class, 'index'])->name('bookings');
    Route::delete('/admin/bookings/{id}', [BookingController::class, 'destroy'])->name('booking.destroy');
    Route::get('/admin/{user}', 'App\Http\Controllers\StationController@userData')->name('userData');
    Route::post('/admin/check', 'App\Http\Controllers\StationController@check')->name('check');
    Route::delete('/admin/users/{id}', 'App\Http\Controllers\StationController@userDelete')->name('users.destroy');
    Route::post('/editUser', 'App\Http\Controllers\StationController@editUser')->name('editUser');

});

Route::group(['middleware' => ['concierge']],function(){
    Route::get('/concierge/scanner', 'App\Http\Controllers\ConciergeController@index')->name('concierge.index');
});



Route::group(['middleware' => ['client']], function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/reward/{reward}', 'App\Http\Controllers\RewardController@index')->name('reward.index');

    Route::get('/thankyou', function () {
        return view('thankyou');
    })->name('thankyou');

    // Referrals
    Route::get('/referrals', 'App\Http\Controllers\ReferralsController@index')->name('referrals.index');

    Route::get('/station/{station}', 'App\Http\Controllers\StationController@index')->name('station');
    Route::get('/dashboard', 'App\Http\Controllers\StationController@welcome')->name('dashboard');
    Route::get('/redemption', 'App\Http\Controllers\StationController@redemption')->name('redemption');
    Route::get('/discover', 'App\Http\Controllers\StationController@discover')->name('discover');
    Route::get('/giftselection', 'App\Http\Controllers\StationController@giftSelection')->name('station.giftselection');
    Route::post('/giftselection/redeem', 'App\Http\Controllers\StationController@redeemGift')->name('giftselection.redeem');
    Route::post('/process_qr_code', 'App\Http\Controllers\StationController@scan')->name('process_qr_code');
    Route::post('/process_stamp', 'App\Http\Controllers\StationController@stamp')->name('process_stamp');
    Route::get('/station/{station}/stamping', 'App\Http\Controllers\StationController@stamping')->name('station.stamping');





    Route::get('/station/{station}/extension', 'App\Http\Controllers\StationController@extension')->name('station.extension');
    Route::get('/station/{station}/brand', 'App\Http\Controllers\StationController@brand')->name('station.brand');
    Route::get('/puzzle', 'App\Http\Controllers\StationController@puzzle')->name('station.puzzle');
    Route::get('/brands', 'App\Http\Controllers\StationController@brands')->name('station.brands');
    Route::get('/workshop/register', 'App\Http\Controllers\WorkshopController@register')->name('workshop.register');
    Route::get('/workshop/check', 'App\Http\Controllers\WorkshopController@check')->name('workshop.check');
    Route::get('/workshop/submit', 'App\Http\Controllers\WorkshopController@submit')->name('workshop.submit');
    Route::get('/workshop/congrats', 'App\Http\Controllers\WorkshopController@congrats')->name('workshop.congrats');
    Route::get('/workshop', 'App\Http\Controllers\WorkshopController@index')->name('workshop');
    Route::get('/pledge-dj', 'App\Http\Controllers\StationController@pledgeDj')->name('pledgeDj');


    Route::get('/promotion', function () {
        return view('promotion');
    })->name('promotion');


    Route::post('/upload', 'App\Http\Controllers\StationController@uploadBaby')->name('upload.baby');

    Route::get('/otp', function () {
        return view('auth.otp');
    })->name('otp');

    Route::get('/resend-otp', 'App\Http\Controllers\StationController@resend')->name('resend.otp');
    Route::post('/verify-otp', 'App\Http\Controllers\StationController@verify')->name('verify.otp');

     Route::get('/register-welcome', function () {
        return view('registerSuccess');
    })->name('register.welcome');

});



require __DIR__ . '/auth.php';
