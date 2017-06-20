<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Access_Token extends Model
{
    //

    protected $fillable = [
        'nus_id', 'token', 'expired_date'
    ];

    const CREATED_AT = 'created_date';

    public function user()
    {
    	return $this->belongsTo('App\User', 'nus_id', 'nus_id');
    }
}
