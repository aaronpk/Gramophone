<?php
namespace App\Http\Controllers;

use Request, DB;
use App\User, App\Podcast;

class PodcastController extends Controller
{

  public function __construct() {
    $this->middleware('auth');
  }

  public function create() {
    return view('podcast/new');
  }

  public function podcast(Podcast $podcast) {

  }

}

