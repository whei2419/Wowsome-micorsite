<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RewardController extends Controller
{
    //
    public function index()
    {
        $user = Auth::user();
        $userHash = hash('sha256', $user->id);

        return view('rewards', [
            'user' => $user,
            'userHash' => $userHash
        ]);
    }
}
