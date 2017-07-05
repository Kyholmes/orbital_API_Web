<?php

namespace App\Http\Controllers\API;

// use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GetCurrentTimeController;
use App\User;
use App\Tag;
use App\Subscription_Tag;
use Request;
use Validator;
use Input;
use Hash;

class ApiTagController extends ApiController
{
    //

    public function __construct()
    {
    	$this->middleware('api_auth');

    	$this->middleware('token_auth');

    	parent::__construct();
    }

    public function add()
    {
    	$v = Validator::make(Input::all(), [
    			'tag_name' => 'required'	
    	]);

    	if($v->fails())
    	{
    		return $this->errorWrongArgs($v->errors());
    	}

    	$post = Input::all();

    	$get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

    	$get_exist_tag = Tag::where('tag', $post['tag_name'])->first();

    	if($get_exist_tag == null)
    	{
    		$new_tag = new Tag();

	    	$new_tag->tag = $post['tag_name'];
	    	
	    	$new_tag->description = $post['description'];

			$current_time = GetCurrentTimeController::getCurrentTime();

	    	$new_tag->last_update = $current_time;

	    	$new_tag->subscribe_no = 1;

	    	$new_tag->created_by = $get_nus_id;

	    	$insert_success = $new_tag->save();

	    	if($insert_success)
	    	{
	    		$addSuccess = ApiTagController::addNewSubscription($get_nus_id, $new_tag->id, $current_time);

	    		if($addSuccess)
	    		{
	    			return $this->successNoContent();
	    		}
	    	}
	    	
	    	return $this->errorInternalError('server down');
    	}
    	else
    	{
    		return $this->errorConflict('this tag is already created');
    	}
    }

    public function update()
    {
    	$v = Validator::make(Input::all(), [
    			'tag_id' => 'required'	
    	]);

    	if($v->fails())
    	{
    		return $this->errorWrongArgs($v->errors());
    	}

    	$post = Input::all();

    	$get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

    	$get_exist_tag = Tag::where('tag', $post['tag_name'])->first();

    	if($post['tag_id'] != $get_exist_tag->id) 
    	{
    		return $this->errorConflict('this tag is already created');
    	}

    	$get_tag = Tag::where('id', $post['tag_id'])->first();

    	if($get_tag != null)
    	{
    		$get_tag->tag = $post['tag_name'];

	    	$get_tag->description = $post['description'];

	    	$current_time = GetCurrentTimeController::getCurrentTime();

	    	$get_tag->last_update = $current_time;

	    	$updateSuccess = $get_tag->save();

	    	if($updateSuccess)
	    	{
	    		return $this->successNoContent();
	    	}
	    	else
	    	{
	    		return $this->errorInternalError('server down');
	    	}
    	}
    	else
    	{
    		return $this->errorNotFound('Tag not found');
    	}
    }

    public function addNewSubscription($nus_id, $tag_id, $current_time)
    {
    	$new_subscription_tag = new Subscription_Tag();

    	$new_subscription_tag->nus_id = $nus_id;

    	$new_subscription_tag->tag_id = $tag_id;

    	$new_subscription_tag->last_visit = $current_time;

    	$insert_success = $new_subscription_tag->save();

    	if($insert_success)
    	{
    		return true;
    	}
    	else
    	{
    		return false;
    	}
    }
}
