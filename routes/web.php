<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/login', action: function () {
    return view('auth.admin-login');
});
Route::get('/concierge/login', action: function () {
    return view('auth.concierge-login');
});

Route::get('/concierge', function () {
    return redirect('/concierge/login');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware(['auth', 'verified'])->group(function () {
//     // Dashboard route

// });

Route::get('/lantern/{id}', [StationController::class, 'show'])
    ->name('lantern.view');

Route::get('/livefeed', function () {
    return view('livefeed');
});


Route::get('/lantern/{id}/download', [StationController::class, 'download'])
    ->name('lantern.download');


//client Routes
Route::group(['middleware' => ['client']],function(){
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', [StationController::class, 'dashboard'])->name('dashboard');

    Route::get('/reward/{reward}', 'App\Http\Controllers\RewardController@index')->name('reward.index');
    Route::get('/referrals', 'App\Http\Controllers\ReferralsController@index')->name('referrals.index');

    //static pages
    Route::get('/directory', function () {
        return view('directory');
    })->name('directory');

    Route::get('/linktree', function () {
        return view('linktree');
    })->name('linktree');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
    Route::get('/scanner', [AdminDashboardController::class, 'scanner'])->name('scanner');
    Route::get('/{user}', [AdminDashboardController::class, 'userData'])->name('userData');
   
    Route::delete('/user/{id}', [AdminDashboardController::class, 'userDelete'])->name('userDelete');

    Route::post('verify-otp-admin', [AdminDashboardController::class, 'verifyAdmin'])->name('verifyAdmin');
    Route::post('/editUser', [AdminDashboardController::class, 'editUser'])->name('editUser');
    Route::post('/check', [AdminDashboardController::class, 'check'])->name('check');
    Route::post('/process_qr_code', [AdminDashboardController::class, 'scan'])->name('process_qr_code');
    Route::post('/workshop/scan', [AdminDashboardController::class, 'scan'])->name('workshop.scan');
});

//Concierge Routes
Route::group(['middleware' => ['concierge']],function(){
    Route::get('/concierge/scanner', 'App\Http\Controllers\ConciergeController@index')->name('concierge.index');
    Route::post('/concierge/search-user', 'App\Http\Controllers\ConciergeController@searchUser')->name('concierge.searchUser');
    Route::post('/concierge/search-user-by-hash', 'App\Http\Controllers\ConciergeController@searchUserByHash')->name('concierge.searchUserByHash');
    Route::post('/concierge/claim-reward', 'App\Http\Controllers\ConciergeController@claimReward')->name('concierge.claimReward');
});

require __DIR__.'/auth.php';
