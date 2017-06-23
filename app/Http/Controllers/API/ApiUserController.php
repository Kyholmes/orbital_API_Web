<?php

namespace App\Http\Controllers\API;

// use Illuminate\Http\Request;
use App\Http\Controllers\API\ApiController;
use App\User;
use Request;
use Validator;
use Input;

class ApiUserController extends ApiController
{
    //

    public function __construct()
    {
    	$this->middleware('api_auth');
    	parent::__construct();
    }

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
}
