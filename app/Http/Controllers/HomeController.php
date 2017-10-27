<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Podcast;

class HomeController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function index() {
        $podcasts = Podcast::where('user_id', Auth::user()->id)->get();

        return view('dashboard', [
            'podcasts' => $podcasts
        ]);
    }
}
