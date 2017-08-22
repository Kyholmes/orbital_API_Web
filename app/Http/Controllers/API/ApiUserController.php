<?php

namespace App\Http\Controllers\API;

// use Illuminate\Http\Request;
use App\Http\Controllers\API\ApiController;
use App\User;
use App\Access_Token;
use App\Subscription_Tag;
use App\Subscription_Post;
use App\Tag;
use App\Post;
use Request;
use Validator;
use Input;
use Hash;
use App\Transformer\UserTransformer;
use App\Transformer\SubscriptionTagTransformer;
use App\Transformer\SubscriptionPostTransformer;
use App\Http\Controllers\GetCurrentTimeController;
use App\Http\Controllers\API\ApiPostController;

class ApiUserController extends ApiController
{
    //

    public function __construct()
    {
    	$this->middleware('api_auth');

    	$this->middleware('token_auth', ['only' => ['logout', 'edit_username', 'get_subscribe_tag', 'subscribe_tag', 'get_subscribe_post']]);

    	parent::__construct();
    }

    //register new user (testing only)
    public function register()
    {
    	$v = Validator::make(Input::all(), [
    			'nus_id' => 'required',
    			'name' => 'required',
    			'password' => 'required',
    			'role'=> 'required'	
    	]);

    	if($v->fails())
    	{
    		return $this->errorWrongArgs($v->errors());
    	}

    	$post = Input::all();

    	$new_user = new User();

    	$new_user->nus_id = $post['nus_id'];
    	$new_user->name = $post['name'];

    	$new_user->username = $post['username'];

    	$new_user->role = $post['role'];

    	$new_user->password = bcrypt($post['password']);

    	$new_user->save();
    }

    //get user profile (testing only)
    public function get_profile()
    {
        $get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

        $get_user_profile = User::with(['access_token' => function($q){
            $q->orderBy('created_date', 'desc')->first();
        }])->with('subscribe_tag')->where('nus_id', '=', $get_nus_id)->first();

        if($get_user_profile != null)
        {
            return $this->respondWithItem($get_user_profile, new UserTransformer);
        }

        return $this->errorInternalError('server down');
    }

    //login
    public function login()
    {
    	$v = Validator::make(Input::all(), 
    		[

    		'nus_id'	=> 'required|max:8',
    		'password' => 'required'
    	]);

    	if($v->fails())
    	{
    		return $this->errorWrongArgs($v->errors());
    	}

    	$post = Input::all();

    	$valid_user = User::where('nus_id', '=', $post['nus_id'])->first();

    	if(empty($valid_user))
    	{
    		return $this->errorNotFound('User not found');
    	}
    	else
    	{
    		if(!Hash::check($post['password'], $valid_user->password))
    		{
    			return $this->errorUnauthorized('Wrong password');
    		}
    	}

    	//generate new access token
    	ApiUserController::generate_access_token($valid_user->nus_id);

    	//get current login user
    	$login_user = User::with(['access_token' => function($q){
    		$q->orderBy('created_date', 'desc')->first();
    	}])->with('subscribe_tag')->where('nus_id', '=', $valid_user->nus_id)->first();

    	return $this->respondWithItem($login_user, new UserTransformer);
    }

    //generate new access token
    public function generate_access_token($nus_id)
    {
        //randomly generate access token and whether alrdy existed in the database
        //if yes, generate a new one
    	do
    	{
    		$token = str_random(60);

    		$exist = Access_Token::where('token', $token)->first();
    	}while (!empty($exist));

        //get current timestamp
    	$current_time = GetCurrentTimeController::getCurrentTime();

        //get expired timestamp
    	$expired_date = GetCurrentTimeController::getExpiredTime($current_time, 24);

        //create new token object
    	$new_token = new Access_Token();

    	$new_token->token = $token;

    	$new_token->nus_id = $nus_id;

    	$new_token->created_date = $current_time;

    	$new_token->expired_date = $expired_date;

    	$new_token->save();

    	if(empty($new_token))
    	{
    		return $this->errorInternalError('server down');
    	}
    }

    //logout
    public function logout()
    {
        //get access token from request header
    	$token = (new AuthKeyController)->get_auth_key('auth-key');

    	//delete access token when logout
    	$delete_token = Access_Token::where('token', '=', $token)->delete();

    	if(empty($delete_token))
    	{
    		return $this->errorInternalError('logout failed');
    	}

    	return $this->successNoContent();
    }

    //user change username
    public function edit_username()
    {
    	$v = Validator::make(Input::all(), [
    		'username' => 'required'
    	]);
    	
    	if($v->fails())
    	{
    		return $this->errorWrongArgs($v->errors());
    	}

    	$post = Input::all();

        //get nus id from header
		$get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

        //get user by nus id
		$get_user = User::where('nus_id', $get_nus_id)->first();

        //check if the new username has been taken by other users
		if($get_user->username != $post['username'])
		{
			$checkUsername = User::where('username', $post['username'])->exists();

	    	if($checkUsername)
	    	{
	    		return $this->errorConflict('this username has been taken');
	    	}
		}

        //change username
    	$get_user->username = $post['username'];

    	$get_user->save();

    	if($get_user)
    	{
    		return $this->respondWithItem($get_user, new UserTransformer);
    	}
    	else
    	{
    		return $this->errorInternalError('server down');
    	}
    }

    //get all subscribed tags
    public function get_subscribe_tag()
    {
    	$post = Input::all();

        //get nus id from auth key
        $get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

        //get all subscribed tags by nus id
    	$get_all_subscription_tag = Subscription_Tag::where('nus_id', $get_nus_id)->get();

    	if($get_all_subscription_tag != null)
    	{
    		return $this->respondWithCollection($get_all_subscription_tag, new SubscriptionTagTransformer, 'subscription_tag');
    	}

    	return $this->errorInternalError('server down');
    }

    //subscribe a tag
    public function subscribe_tag()
    {   
        //check if tag id parameter exist
        if(!Input::has('tag_id'))
        {
            return $this->errorWrongArgs('tag_id field is required');
        }

        $post = Input::all();

        $get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

        $current_time = GetCurrentTimeController::getCurrentTime();

        //check if tag exist
        $get_tag = Tag::where('id', $post['tag_id'])->first();

        //if not exist, return not found error message
        if($get_tag == null)
        {
            return $this->errorNotFound('tag not found');
        }

        //add new tag subscription into database
        $subscribeSuccess = ApiTagController::addNewSubscriptionTag($get_nus_id, $post['tag_id'], $current_time);

        //if add success
        if($subscribeSuccess)
        {
            $get_tag->subscribe_no = $get_tag->subscribe_no + 1;

            $save_success = $get_tag->save();

            if($save_success)
            {
                $get_new_subscribed_tag = Subscription_Tag::where('tag_id', $post['tag_id'])->first();

                return $this->respondWithItem($get_new_subscribed_tag, new SubscriptionTagTransformer, 'subscription_tag');
            }
            
        }

        return $this->errorInternalError('server down');
    }

    //get all subscribed posts
    public function get_subscribe_post()
    {
        $get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

        //get all subscribed posts by nus id
        $get_all_post = Subscription_Post::where('nus_id', $get_nus_id)->get();

        //check if get success
        if($get_all_post != null)
        {
            return $this->respondWithCollection($get_all_post, new SubscriptionPostTransformer, 'subscription_post');
        }

        return $this->errorInternalError('server down');
    }

    //unsubscribe tags
    public function unsubscribe_tag()
    {
        //check if tag id parameter exist
        if(!Input::has('tag_id'))
        {
            return $this->errorWrongArgs('tag_id field is required');
        }

        $post = Input::all();

        $get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

        //check if user subscribe this tag
        $get_subscribed_tag = Subscription_Tag::where(['tag_id' => $post['tag_id'], 'nus_id' => $get_nus_id])->first();

        //if user doesnt subscribe this tag, return error message
        if($get_subscribed_tag == null)
        {
            return $this->errorNotFound('this tag subscription is not found in the database');
        }

        //delete the tag subscription
        $deleteSuccess = $get_subscribed_tag->delete();

        //if delete success
        if($deleteSuccess)
        {
            $get_tag = Tag::where('id', $post['tag_id'])->first();

            $get_tag->subscribe_no = $get_tag->subscribe_no - 1;

            $save_success = $get_tag->save();

            if($save_success)
            {
               return $this->successNoContent(); 
            }
        }

        return $this->errorInternalError('server down');
    }

    //subscribe a post
    public function subscribe_post()
    {
        //check if post id parameter exist
        if(!Input::has('post_id'))
        {
            return $this->errorWrongArgs('post_id field is required');
        }

        $post = Input::all();

        $get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

        //get current time
        $current_time = GetCurrentTimeController::getCurrentTime();

        //check if post exist
        $get_post = Post::where('id', $post['post_id'])->first();

        //if not exist, return not found error message
        if($get_post == null)
        {
            return $this->errorNotFound('post not found');
        }

        $addNewSubSuccess = ApiPostController::addNewSubscriptionPost($get_nus_id, $post['post_id'], $current_time);

        if($addNewSubSuccess)
        {
            $get_post->subscribe_no = $get_post->subscribe_no + 1;

            $save_success = $get_post->save();

            if($save_success)
            {
                return $this->successNoContent();
            }
        }

        return $this->errorInternalError('server down');
    }

    //unsubscribe a post
    public function unsubscribe_post()
    {
        //check if post id parameter exist
        if(!Input::has('post_id'))
        {
            return $this->errorWrongArgs('post_id field is required');
        }

        $post = Input::all();

        $get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

        //check if user subscribe this post
        $get_subscribed_post = Subscription_Post::where(['post_id' => $post['post_id'], 'nus_id' => $get_nus_id])->first();

        //if user doesnt subscribe this post, return error message
        if($get_subscribed_post == null)
        {
            return $this->errorNotFound('this post subscription is not found in the database');
        }

        //delete the post subscription
        $deleteSuccess = $get_subscribed_post->delete();

        //if delete success
        if($deleteSuccess)
        {
            $get_post = Post::where('id', $post['post_id'])->first();

            $get_post->subscribe_no = $get_post->subscribe_no - 1;

            $save_success = $get_post->save();

            if($save_success)
            {
               return $this->successNoContent(); 
            }
        }

        return $this->errorInternalError('server down');
    }

    public function update_last_seen()
    {
        $get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

        $get_user = User::where('nus_id', $get_nus_id)->first();

        if($get_user != null)
        {
            $get_user->notification_last_seen = GetCurrentTimeController::getCurrentTime();

            $update_success = $get_user->save();

            if($update_success)
            {
                return $this->successNoContent();
            }
        }

        return $this->errorInternalError('server down');
    }
}
