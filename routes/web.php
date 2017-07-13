<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api/v1')->group(function(){
	
	Route::post('/register', 'API\ApiUserController@register');

	//user
	Route::get('/user', 'API\ApiUserController@get_profile');

	Route::post('/user/login', 'API\ApiUserController@login');

	Route::delete('/user/logout', 'API\ApiUserController@logout');

	Route::delete('/user/logout', 'API\ApiUserController@logout');

	Route::put('/user/update', 'API\ApiUserController@edit_username');

	Route::get('/user/tags', 'API\ApiUserController@get_subscribe_tag');

	Route::post('/user/tags', 'API\ApiUserController@subscribe_tag');

	//tag
	Route::get('/tags', 'API\ApiTagController@get');

	Route::post('/tags', 'API\ApiTagController@add');

	Route::put('/tags', 'API\ApiTagController@update');

	Route::delete('/tags', 'API\ApiTagController@delete');

	Route::get('/tags/post', 'API\ApiTagController@get_post');

	//post
	Route::get('/post', 'API\ApiPostController@get');

	Route::post('/post', 'API\ApiPostController@add');
});
