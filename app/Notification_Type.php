<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification_Type extends Model
{
    //
    protected $fillable = [
        'description'
    ];

    public $timestamps = false;

    public function notification()
    {
    	return $this->hasMany('App\Notification', 'notification_type', 'id');
    }
}
