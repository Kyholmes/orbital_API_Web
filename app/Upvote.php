<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Upvote extends Model
{
    //
     protected $fillable = [
        'nus_id', 'post_id', 'comment_id'
    ];

    public $timestamps = false;

    public function user()
    {
    	return $this->belongsTo('App\User', 'nus_id', 'nus_id');
    }
}
