<?php

namespace App\Http\Controllers\API;

// use Illuminate\Http\Request;
use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\GetCurrentTimeController;
use App\Transformer\CommentTransformer;
use App\Comment;
use Request;
use Validator;
use Input;
use Hash;

class ApiCommentController extends ApiController
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
    			'description' => 'required',
    			'post_id' => 'required'
    	]);

    	if($v->fails())
    	{
    		return $this->errorWrongArgs($v->errors());
    	}

		$post = Input::all();

		$get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

		$new_comment = new Comment();

		$new_comment->description = $post['description'];

		$new_comment->nus_id = $get_nus_id;

		$new_comment->post_id = $post['post_id'];

		if(Input::has('reply_to_id'))
		{
			$new_comment->reply_to_nus_id = $post['reply_to_id'];
		}

		if(Input::has('image_link'))
		{
			$new_comment->img_link = $post['image_link'];
		}

		$save_success = $new_comment->save();

		if($save_success)
		{
			return $this->respondWithItem($new_comment, new CommentTransformer, 'comment');
		}

		return $this->errorInternalError('server down');
    }

    public function edit()
    {
    	$v = Validator::make(Input::all(), [
    			'description' => 'required',
    			'comment_id' => 'required'
    	]);

    	if($v->fails())
    	{
    		return $this->errorWrongArgs($v->errors());
    	}

    	$post = Input::all();

    	$edit_comment = Comment::where('id', $post['comment_id'])->first();

    	$edit_comment->description = $post['description'];

		if(Input::has('image_link'))
		{
			$edit_comment->img_link = $post['image_link'];
		}

		$save_success = $edit_comment->save();

		if($save_success)
		{
			return $this->successNoContent();
		}

		return $this->errorInternalError('server down');
    }

    public function delete()
    {
    	if(!Input::has('comment_id'))
    	{
    		return $this->errorWrongArgs('comment_id field is required');
    	}

    	$post = Input::all();

    	$delete_success = Comment::where('id', $post['comment_id'])->delete();

    	if($delete_success)
    	{
    		return $this->successNoContent();
    	}

    	return $this->errorInternalError('server down');
    }

    public static function deleteAllComments($post_id)
    {
    	$delete_success = Comment::where('post_id', $post_id)->delete();

    	if(!$delete_success)
    	{
    		return $this->errorInternalError('server down');
    	}
    }
}
