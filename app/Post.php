<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    //
    protected $fillable = [
        'img_link', 'title', 'question_descrip', 'vote', 'subscribe_no', 'expired_date', 'time_limit', 'nus_id', 'points'
    ];

    const CREATED_AT = 'created_date';

    const UPDATED_AT = 'updated_date';

    public function user()
    {
    	return $this->belongsTo('App\User', 'nus_id', 'nus_id');
    }

    public function tag_post()
    {
    	return $this->hasMany('App\Tag_Post', 'post_id', 'id');
    }

    public function subscribe_post()
    {
        return $this->hasMany('App\Subscription_Post', 'post_id', 'id');
    }

    public function comment()
    {
        return $this->hasMany('App\Comment', 'post_id', 'id');
    }
}
