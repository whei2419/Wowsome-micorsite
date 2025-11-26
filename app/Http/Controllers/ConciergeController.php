<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConciergeController extends Controller
{
    //
    public function index()
    {
        return view('conciergeScanner');
    }
}
