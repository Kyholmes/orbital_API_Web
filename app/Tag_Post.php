<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag_Post extends Model
{
    //
    protected $fillable = [
        'post_id', 'tag_id'
    ];

    public $timestamps = false;

    public function tag()
    {
    	return $this->belongsTo('App\Tag', 'tag_id', 'id');
    }

    public function post()
    {
    	return $this->belongsTo('App\Post', 'post_id', 'id');
    }
}
