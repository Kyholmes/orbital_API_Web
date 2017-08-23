<?php

namespace App\Http\Controllers\API;

// use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GetCurrentTimeController;
use App\Notification;
use App\User;
use App\Transformer\NotificationUpdateTransformer;
use App\Transformer\NotificationTransformer;
use Request;
use Validator;
use Input;

class ApiNotificationController extends ApiController
{
    //

    public function __construct()
    {
    	$this->middleware('api_auth');

    	$this->middleware('token_auth');

    	parent::__construct();
    }

    public function getUpdate()
    {
    	$get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

    	$getUser = User::where('nus_id', $get_nus_id)->first();

    	// $update = Notification::where(['nus_id', '=', $get_nus_id], ['created_date','>', $getUser->notification_last_seen])->get();

    	// $update = Notification::where('created_date','>', $getUser->notification_last_seen)->get();

    	// $update = $update->where('nus_id', $get_nus_id)->where('read', '!=', 1)->all();

    	$update = Notification::where('nus_id', $get_nus_id)->where('read', '!=', 1)->get();

    	if(sizeof($update) > 0)
    	{
			return $this->respondWithItem(sizeof($update), new NotificationUpdateTransformer, 'notification_update');
    	}
    	else
    	{
			return $this->respondWithItem(0, new NotificationUpdateTransformer, 'notification_update');
    	}

    	return $this->errorInternalError('server down');
    }

    public function get()
    {
    	$get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

    	$getAllNotification = Notification::where('nus_id', $get_nus_id)->orderBy('created_date', 'desc')->get();

    	if($getAllNotification != null)
    	{
    		return $this->respondWithCollection($getAllNotification, new NotificationTransformer, 'notification');
    	}

    	return $this->errorInternalError('server down');
    }

    public function update()
    {
    	if(!Input::has('notification_id'))
    	{
    		return $this->errorWrongArgs('notification_id field is required');
    	}

    	$post = Input::all();

    	$get_notification = Notification::where('id', $post['notification_id'])->first();

    	if($get_notification == null)
    	{
    		return $this->errorNotFound('this notification cannot be found');
    	}

    	$get_notification->read = 1;

    	$update_success = $get_notification->save();

    	if($update_success)
    	{
    		return $this->successNoContent();
    	}

    	return $this->errorInternalError('server down');
    }

    public static function addNotification($nus_id, $notification_type, $comment_id, $post_id, $tag_id)
    {
    	//1 --> module tag update
    	//2 --> new comment for post
    	//3 --> new comment for comment
    	//4 --> answer pin as the best answer

    	$new_notification = new Notification();

    	$new_notification->nus_id = $nus_id;

    	$new_notification->notification_type = $notification_type;

    	$new_notification->comment_id = $comment_id;

    	$new_notification->post_id = $post_id;

    	$new_notification->tag_id = $tag_id;

    	$new_notification->created_date = GetCurrentTimeController::getCurrentTime();

    	$save_success = $new_notification->save();

    	if($save_success)
    	{
    		return true;
    	}

    	return false;
    }
}
