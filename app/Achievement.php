<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    //
    protected $fillable = [
        'nus_id', 'question_no', 'answer_no', 'comment_no', 'points', 'achievement_no'
    ];

    public $timestamps = false;

    public function user()
    {
    	return $this->belongsTo('App\User', 'nus_id', 'nus_id');
    }
}
