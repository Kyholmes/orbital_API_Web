<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    //
    protected $fillable = [
        'tag', 'description', 'created_by', 'last_update', 'subscribe_no', 'status'
    ];

    public $timestamps = false;

    public function user()
    {
    	return $this->belongsTo('App\User', 'created_by', 'nus_id');
    }

    public function subscribe_tag()
    {
        return $this->hasMany('App\Subscription_Tag', 'tag_id', 'id');
    }

    public function tag_post()
    {
    	return $this->hasMany('App\Tag_Post', 'tag_id', 'id');
    }

    public function notification()
    {
    	return $this->hasMany('App\Notification', 'tag_id', 'id');
    }
}
