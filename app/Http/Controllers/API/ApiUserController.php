<?php

namespace App\Http\Controllers\API;

// use Illuminate\Http\Request;
use App\Http\Controllers\API\ApiController;
use App\User;
use App\Access_Token;
use Request;
use Validator;
use Input;
use Hash;
use App\Transformer\UserTransformer;

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
    			'username' => 'required',
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
    	ob_start();
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

    	// $expired_date = $current_time + (24 * 60 * 60);
    	$expired_date = $current_time + 1;

    	$datetimeFormat = 'Y-m-d H:i:s';

		$current = new \DateTime();

		$expired = new \DateTime();

		$current->setTimestamp($current_time);

		$expired->setTimestamp($expired_date);
		// var_dump($date->format($datetimeFormat));
		// die();
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
    public function edit_profile()
    {
    	$v = Validator::make(Input::all(), [
    		'username' => 'required|unique:user'
    	]);

    	if($v->fails())
    	{
    		return $this->errorWrongArgs($v->errors());
    	}

    	$post = Input::all();

    	$get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

    	// $updateDetail = ['username' => $post['username']];

    	$get_user = User::where('nus_id', $get_nus_id)->get();

    	$get_user->username = $post['username'];

    	$update = $get_user->save();

    	if($update)
    	{
    		return $this->respondWithArray(array('success' => ['code' => 'SUCCESS', 'http_code' => 200, 'message' => 'username updated']), array());
    	}
    	else
    	{
    		return $this->errorInternalError('server down');
    	}
    }
}
