<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
  public function podcast() {
    return $this->belongsTo('App\Podcast');
  }
}
