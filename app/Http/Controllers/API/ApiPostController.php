<?php

namespace App\Http\Controllers\API;

// use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GetCurrentTimeController;
use App\Post;
use App\Subscription_Post;
use App\Tag_Post;
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
}
