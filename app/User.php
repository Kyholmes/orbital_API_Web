<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nus_id', 'name', 'password', 'username', 'role', 'notification_last_seen'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    public $timestamps = false;

    public function access_token()
    {
        return $this->hasOne('App\Access_Token', 'nus_id', 'nus_id');
    }

    public function upvote()
    {
        return $this->hasMany('App\Upvote', 'nus_id', 'nus_id');
    }

    public function achievement()
    {
        return $this->hasOne('App\Achievement', 'nus_id', 'nus_id');
    }

    public function tag()
    {
        return $this->hasMany('App\Tag', 'created_by', 'nus_id');
    }

    public function subscribe_tag()
    {
        return $this->hasMany('App\Subscription_Tag', 'nus_id', 'nus_id');
    }

    public function post()
    {
        return $this->hasMany('App\Post', 'nus_id', 'nus_id');
    }

    public function subscribe_post()
    {
        return $this->hasMany('App\Subscription_Post', 'nus_id', 'nus_id');
    }

    public function comment()
    {
        return $this->hasMany('App\Comment', 'nus_id', 'nus_id');
    }

    public function notification()
    {
        return $this->hasMany('App\Notification', 'nus_id', 'nus_id');
    }
}
