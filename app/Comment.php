<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //
    protected $fillable = [
        'description', 'vote', 'best_answer', 'reply_to_nus_id', 'nus_id', 'post_id', 'img_link', 'comment_id'
    ];

    const CREATED_AT = 'created_date';

    const UPDATED_AT = 'updated_date';

    public function user()
    {
    	return $this->belongsTo('App\User', 'nus_id', 'nus_id');
    }

    public function post()
    {
    	return $this->belongsTo('App\Post', 'post_id', 'id');
    }

    public function parent()
    {
    	return $this->belongsTo('Comment', 'comment_id', 'id');
    }

    public function children()
    {
    	return $this->hasMany('Comment', 'comment_id', 'id');
    }

    public function notification()
    {
    	return $this->hasMany('App\Notification', 'comment_id', 'id');
    }
}
