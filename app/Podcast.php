<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Podcast extends Model
{
  public function podcast() {
    return $this->belongsTo('App\User');
  }
}