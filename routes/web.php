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
});

//Concierge Routes
Route::group(['middleware' => ['concierge']],function(){
    Route::get('/concierge/scanner', 'App\Http\Controllers\ConciergeController@index')->name('concierge.index');
});

require __DIR__.'/auth.php';
