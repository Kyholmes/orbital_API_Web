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

    //get all tags
    public function get()
    {
        //get all tags that are in 'open' status
    	$get_all_tag = Tag::where('status', 1)->get();

        //check if get success
    	if($get_all_tag != null)
    	{
    		return $this->respondWithCollection($get_all_tag, new TagTransformer, 'tag');
    	}
    	
    	return $this->errorInternalError('server down');
    }

    //add new tag
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

        //check if the tag exist in the database
    	$get_exist_tag = Tag::where('tag', $post['tag_name'])->first();

        //if not, add the tag into database and add new tag subscription for the tag owner
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

            //if new tag created successfully
	    	if($insert_success)
	    	{
	    		$addSuccess = ApiTagController::addNewSubscriptionTag($get_nus_id, $new_tag->id, $current_time);

                //if tag subscription added successfully
	    		if($addSuccess)
	    		{
                    $get_new_tag = Subscription_Tag::where('tag_id', $new_tag->id)->first();

	    			return $this->respondWithItem($get_new_tag, new SubscriptionTagTransformer, 'subscription_tag');
	    		}
                //if adding tag subscription is not successful, delete the newly created tag
                else
                {
                    $new_tag->delete();
                }
	    	}
	    	
	    	return $this->errorInternalError('server down');
    	}
    	else
    	{
    		return $this->errorConflict('this tag is already created');
    	}
    }

    //update tag detail
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

        //check if the tag name has been taken
    	if($get_exist_tag != null && $post['tag_id'] != $get_exist_tag->id) 
    	{
    		return $this->errorConflict('this tag is already created');
    	}

    	$get_tag = Tag::where('id', $post['tag_id'])->first();

        //check if the tag exist in database
    	if($get_tag != null)
    	{  
            //check if tag is created by the current user
            if(strcasecmp($get_nus_id, $get_tag->created_by) != 0)
            {
                return $this->errorUnauthorized('only tag owner are allow to edit tag detail');
            }

            //update the tag detail
    		$get_tag->tag = $post['tag_name'];

	    	$get_tag->description = $post['description'];

	    	$current_time = GetCurrentTimeController::getCurrentTime();

	    	$get_tag->last_update = $current_time;

	    	$get_tag->status = $post['tag_status'];

	    	$updateSuccess = $get_tag->save();

            //check if tag details updated successfully
	    	if($updateSuccess)
	    	{
	    		return $this->successNoContent();
	    	}
	    	
	    	return $this->errorInternalError('server down');
    	}
    	else
    	{
    		return $this->errorNotFound('Tag not found');
    	}
    }

    //delete tag
    public function delete()
    {
    	if(!Input::has('tag_id'))
    	{
    		return $this->errorWrongArgs('tag_id field is required');
    	}

    	$post = Input::all();

    	$get_tag = Tag::where('id', $post['tag_id'])->first();

        //check if the tag have subscription other than the tag owner
    	if($get_tag->subscribe_no > 1)
    	{
    		return $this->errorForbidden('tag cannot be deleted, subscriptions exist');
    	}

        //check if there are posts with this tag, if yes this tag cannot be deleted
    	$deleteAllow = ApiTagController::CheckPostExist($post['tag_id']);

        if(!$deleteAllow)
        {
            return $this->errorForbidden('tag cannot be deleted, posts with this tag exist');
        }

    	$delete_subscription_tag = Subscription_Tag::where('tag_id', $post['tag_id'])->delete();

        //if delete success
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

    //add new tag subscription for the user (tag owner) when the user create a tag
    public static function addNewSubscriptionTag($nus_id, $tag_id, $current_time)
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

    //check if there are posts with the tag
    public function CheckPostExist($tag_id)
    {
    	$post_exist = Tag_Post::where('tag_id', $tag_id)->get();

    	if(sizeof($post_exist) > 0)
    	{
    		return false;
    	}

        return true;
    }

    //get all posts with the tag
    public function get_post()
    {
        if(!Input::has('tag_id'))
        {
            return $this->errorWrongArgs('tag_id field is required');
        }

        $post = Input::all();

        //get all posts by tag id
        $get_all_post = Tag_Post::where('tag_id', $post['tag_id'])->get();

        if($get_all_post != null)
        {
            return $this->respondWithCollection($get_all_post, new TagPostTransformer, 'tag_post');
        }

        return $this->errorInternalError('server down');
    }
}
