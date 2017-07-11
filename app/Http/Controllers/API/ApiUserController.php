<?php

namespace App\Http\Controllers\API;

// use Illuminate\Http\Request;
use App\Http\Controllers\API\ApiController;
use App\User;
use App\Access_Token;
use App\Subscription_Tag;
use Request;
use Validator;
use Input;
use Hash;
use App\Transformer\UserTransformer;
use App\Transformer\SubscriptionTagTransformer;
use App\Http\Controllers\GetCurrentTimeController;

class ApiUserController extends ApiController
{
    //

    public function __construct()
    {
    	$this->middleware('api_auth');

    	$this->middleware('token_auth', ['only' => ['edit_profile']]);

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
    	$new_access_token = ApiUserController::generate_access_token($valid_user->nus_id);

    	//get current login user
    	$login_user = User::with(['access_token' => function($q){
    		$q->orderBy('created_date', 'desc')->first();
    	}])->with('subscribe_tag')->where('nus_id', '=', $valid_user->nus_id)->first();

    	return $this->respondWithItem($login_user, new UserTransformer);
    }

    //generate new access token
    public function generate_access_token($nus_id)
    {
    	do
    	{
    		$token = str_random(60);

    		$exits = Access_Token::where('token', $token)->first();
    	}while (!empty($exits));

    	date_default_timezone_set('Asia/Singapore');

    	$current_time = time();

    	$expired_date = $current_time + (24 * 60 * 60);
    	// $expired_date = $current_time + 1;

    	$datetimeFormat = 'Y-m-d H:i:s';

		$current = new \DateTime();

		$expired = new \DateTime();

		$current->setTimestamp($current_time);

		$expired->setTimestamp($expired_date);

    	$new_token = new Access_Token();

    	$new_token->token = $token;

    	$new_token->nus_id = $nus_id;

    	$new_token->created_date = $current->format($datetimeFormat);

    	$new_token->expired_date = $expired->format($datetimeFormat);

    	$new_token->save();

    	if(empty($new_token))
    	{
    		return $this->errorInternalError('server down');
    	}

    	return $new_token;
    }

    //logout
    public function logout()
    {
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

		$get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

		$get_user = User::where('nus_id', $get_nus_id)->first();

		if($get_user->username == "" || $get_user->username != $post['username'])
		{
			$checkUsername = User::where('username', $post['username'])->exists();

	    	if($checkUsername)
	    	{
	    		return $this->errorConflict('this username has been taken');
	    	}
		}

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

    public function get_subscribe_tag()
    {
    	if(!Input::has('nus_id'))
    	{
    		return $this->errorWrongArgs('nus_id field is required');
    	}

    	$post = Input::all();

    	$get_all_subscription_tag = Subscription_Tag::where('nus_id', $post['nus_id'])->get();

    	if($get_all_subscription_tag != null)
    	{
    		return $this->respondWithCollection($get_all_subscription_tag, new SubscriptionTagTransformer, 'subscription_tag');
    	}

    	return $this->errorInternalError('server down');
    }

    public function subscribe_tag()
    {
        if(!Input::has('tag_id'))
        {
            return $this->errorWrongArgs('tag_id field is required');
        }

        $post = Input::all();

        $get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

        $current_time = GetCurrentTimeController::getCurrentTime();

        $subscribeSuccess = ApiTagController::addNewSubscriptionTag($get_nus_id, $post['tag_id'], $current_time);

        if($subscribeSuccess)
        {
            $get_new_subscribed_tag = Subscription_Tag::where('tag_id', $post['tag_id'])->first();

            return $this->respondWithItem($get_new_subscribed_tag, new SubscriptionTagTransformer, 'subscription_tag');
        }

        return $this->errorInternalError('server down');
    }
}
