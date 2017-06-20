<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription_Tag extends Model
{
    //
    protected $fillable = [
        'nus_id', 'tag_id', 'last_visit'
    ];

    public $timestamps = false;

    public function user()
    {
    	return $this->belongsTo('App\User', 'nus_id', 'nus_id');
    }

    public function tag()
    {
    	return $this->belongsTo('App\Tag', 'tag_id', 'id');
    }
}
