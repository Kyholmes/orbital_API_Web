<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription_Post extends Model
{
    //
    protected $fillable = [
        'post_id', 'nus_id', 'last_visit'
    ];

    public $timestamps = false;

    public function user()
    {
    	return $this->belongsTo('App\User', 'nus_id', 'nus_id');
    }

    public function post()
    {
    	return $this->belongsTo('App\Post', 'post_id', 'id');
    }
}
