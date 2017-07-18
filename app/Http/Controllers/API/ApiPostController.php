<?php

namespace App\Http\Controllers\API;

// use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GetCurrentTimeController;
use App\Http\Controllers\API\ApiCommentController;
use App\Post;
use App\Subscription_Post;
use App\Tag_Post;
use App\Transformer\PostTransformer;
use Request;
use Validator;
use Input;

class ApiPostController extends ApiController
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
    	if(!Input::has('post_id'))
    	{
    		return $this->errorWrongArgs('post_id field is required');
    	}

    	$post = Input::all();

    	$get_post = Post::where('id',$post['post_id'])->first();

    	if($get_post != null)
    	{
    		return $this->respondWithItem($get_post, new PostTransformer, 'post');
    	}

    	return $this->errorInternalError('server down');
    }

    public function add()
    {
    	$v = Validator::make(Input::all(), [
    			'title' => 'required',
    			'description' => 'required_without:image_link',
    			'tag_id' => 'required|array'
    	]);

    	if($v->fails())
    	{
    		return $this->errorWrongArgs($v->errors());
    	}

    	$post = Input::all();

    	$get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

    	$newPost = new Post();

    	$newPost->title = $post['title'];

    	if(Input::has('description'))
    	{
    		$newPost->question_descrip = $post['description'];
    	}

    	if(Input::has('image_link'))
    	{
    		$newPost->img_link = $post['image_link'];
    	}

    	$newPost->nus_id = $post['nus_id'];

    	$addSuccess = $newPost->save();

    	if($addSuccess)
    	{
    		ApiPostController::addPostToTag($post['tag_id'], $newPost->id);

    		$current_time = GetCurrentTimeController::getCurrentTime();

    		ApiPostController::addNewSubscriptionPost($get_nus_id, $newPost->id, $current_time);

    		return $this->successNoContent();
    	}

    	return $this->errorInternalError('server down');
    }

    public function delete()
    {
    	if(!Input::has('post_id'))
    	{
    		return $this->errorWrongArgs('post_id field is required');
    	}

    	if(!Input::has('nus_id'))
    	{
    		return $this->errorWrongArgs('nus_id field is required');
    	}

    	// if(!Input::has('role'))
    	// {
    	// 	return $this->errorWrongArgs('role_id field is required');
    	// }

    	$post = Input::all();

    	ApiPostController::deleteSubscriptionPost($post['post_id']);

    	ApiPostController::deleteTagPost($post['post_id']);

    	ApiCommentController::deleteAllComments($post['post_id']);

    	$delete_post = Post::where('id', $post['post_id'])->delete();

    	if($delete_post)
    	{
    		return $this->successNoContent();
    	}

    	return $this->errorInternalError('server down');
    }

    public function edit()
    {
    	$v = Validator::make(Input::all(), [
    			'post_id' => 'required',
    			'title' => 'required',
    			'description' => 'required_without:image_link'
    	]);

    	if($v->fails())
    	{
    		return $this->errorWrongArgs($v->errors());
    	}

    	$post = Input::all();

    	$get_post = Post::where('id', $post['post_id'])->first();

    	$get_post->title = $post['title'];

    	$get_post->question_descrip = $post['description'];

    	if(Input::has('image_link'))
    	{
    		$get_post->img_link = $post['image_link'];
    	}
    	
    	$save_success = $get_post->save();

    	// if(!Input::has('tag_id'))
    	// {
    	// 	$tag = Input::get('tag_id');

    	// 	if(!is_array($tag))
    	// 	{
    	// 		return $this->errorWrongArgs("tag_id must be in array format");
    	// 	}

    	// 	ApiPostController::deleteTagPost($post['post_id']);
    	// }

    	if($save_success)
    	{
    		return $this->successNoContent();
    	}

    	return $this->errorInternalError('server down');
    }

    public function addPostToTag($tag_id_array, $post_id)
    {
    	$data = array();

    	for ($i = 0; $i < sizeof($tag_id_array); $i++) 
    	{ 
    		array_push($data, array('post_id' => $post_id, 'tag_id' => $tag_id_array[$i]));
    	}

    	$insertSuccess = Tag_Post::insert($data);

    	if(!$insertSuccess)
    	{
    		return $this->errorInternalError('server down');
    	}
    }

    public function addNewSubscriptionPost($nus_id, $post_id, $current_time)
    {
    	$new_subscription_post = new Subscription_Post();

    	$new_subscription_post->post_id = $post_id;

    	$new_subscription_post->nus_id = $nus_id;

    	$new_subscription_post->last_visit = $current_time;

    	$addSuccess = $new_subscription_post->save();

    	if(!$addSuccess)
    	{
    		return $this->errorInternalError('server down');
    	}
    }

    public function deleteSubscriptionPost($post_id)
    {
    	$subscription_post = Subscription_Post::where('post_id', $post_id)->delete();

    	if(!$subscription_post)
    	{
    		return $this->errorInternalError('server down');
    	}
    }

    public function deleteTagPost($post_id)
    {
    	$tag_post = Tag_Post::where('post_id', $post_id)->delete();

    	if(!$tag_post)
    	{
    		return $this->errorInternalError('server down');
    	}
    }
}
