<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    //
    protected $fillable = [
        'expired_date', 'read'; 'nus_id', 'notification_type', 'comment_id', 'post_id'
    ];

    const CREATED_AT = 'created_date';

    public function user()
    {
    	return $this->belongsTo('App\User', 'nus_id', 'nus_id');
    }

    public function notification_type()
    {
    	return $this->belongsTo('App\Notification_Type', 'notification_type', 'id');
    }

    public function comment()
    {
    	return $this->belongsTo('App\Comment', 'comment_id', 'id');
    }

    public function post()
    {
    	return $this->belongsTo('App\Post', 'post_id', 'id');
    }

    public function tag()
    {
    	return $this->belongsTo('App\Tag', 'tag_id', 'id');
    }
}
