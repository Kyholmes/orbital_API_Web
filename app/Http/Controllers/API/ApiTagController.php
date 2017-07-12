<?php

namespace App\Http\Controllers\API;

// use Illuminate\Http\Request;
use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\GetCurrentTimeController;
use App\User;
use App\Tag;
use App\Subscription_Tag;
use App\Tag_Post;
use App\Transformer\TagTransformer;
use App\Transformer\SubscriptionTagTransformer;
use App\Transformer\TagPostTransformer;
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

    public function get()
    {
    	$get_all_tag = Tag::where('status', 1)->get();

    	if($get_all_tag != null)
    	{
    		return $this->respondWithCollection($get_all_tag, new TagTransformer, 'tag');
    	}
    	else
    	{
    		return $this->errorInternalError('server down');
    	}
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

	    	$new_tag->status = 1;

	    	$insert_success = $new_tag->save();

	    	if($insert_success)
	    	{
	    		$addSuccess = ApiTagController::addNewSubscriptionTag($get_nus_id, $new_tag->id, $current_time);

	    		if($addSuccess)
	    		{
                    $get_new_tag = Subscription_Tag::where('tag_id', $new_tag->id)->first();

	    			return $this->respondWithItem($get_new_tag, new SubscriptionTagTransformer, 'subscription_tag');
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

    	if($get_exist_tag != null && $post['tag_id'] != $get_exist_tag->id) 
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

	    	$get_tag->status = $post['tag_status'];

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

    public function delete()
    {
    	if(!Input::has('tag_id'))
    	{
    		return $this->errorWrongArgs('tag_id field is required');
    	}

    	$post = Input::all();

    	$get_tag = Tag::where('id', $post['tag_id'])->first();

    	if($get_tag->subscribe_no > 1)
    	{
    		return $this->errorForbidden('tag cannot be deleted, subscriptions exist');
    	}

    	ApiTagController::CheckPostExist($post['tag_id']);

    	$delete_subscription_tag = Subscription_Tag::where('tag_id', $post['tag_id'])->delete();

    	if($delete_subscription_tag)
    	{
    		$delete_tag = Tag::where('id', $post['tag_id'])->delete();

	    	if($delete_tag)
	    	{
	  			return $this->successNoContent();
	    	}
    	}

    	return $this->errorInternalError('server down');
    }

    public function addNewSubscriptionTag($nus_id, $tag_id, $current_time)
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

    public function CheckPostExist($tag_id)
    {
    	$post_exist = Tag_Post::where('tag_id', $tag_id)->get();

    	if($post_exist == null)
    	{
    		return $this->errorForbidden('tag cannot be deleted, posts with this tag exist');
    	}
    }

    public function get_post()
    {
        if(!Input::has('tag_id'))
        {
            return $this->errorWrongArgs('tag_id field is required');
        }

        $post = Input::all();

        $get_all_post = Tag_Post::where('tag_id', $post['tag_id'])->get();

        if($get_all_post != null)
        {
            return $this->respondWithCollection($get_all_post, new TagPostTransformer, 'tag_post');
        }

        return $this->errorInternalError('server down');
    }
}
