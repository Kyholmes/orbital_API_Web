<?php

namespace App\Http\Controllers\API;

// use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Access_Token;
use Request;

class AuthKeyController extends Controller
{
    //

    //get current user auth token
    public function get_auth_key($keyName)
    {
    	$token = Request::header()[$keyName][0];

    	return $token;
    }

    //get current nus id
    public function get_nus_id($keyName)
    {
    	$token = Request::header()[$keyName][0];

    	$user = Access_Token::where('token', $token)->first();

    	return $user->nus_id;
    }
}
