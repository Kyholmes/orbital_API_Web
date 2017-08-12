<?php

namespace App\Http\Controllers\API;

// use Illuminate\Http\Request;
use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\GetCurrentTimeController;
use App\Transformer\CommentTransformer;
use App\Comment;
use App\Post;
use App\Upvote;
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

    //add comment
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

        //get post by post id
		$get_post = Post::where('id', $post['post_id'])->first();

		if($get_post == null)
		{
			return $this->errorNotFound('Post not found');
		}

        //create comment object & assign values
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

    //edit comment
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

        //get comment by comment id
    	$edit_comment = Comment::where('id', $post['comment_id'])->first();

        if($edit_comment == null)
        {
            return $this->errorNotFound('Comment not found');
        }

    	$edit_comment->description = $post['description'];

		if(Input::has('image_link'))
		{
			$edit_comment->img_link = $post['image_link'];
		}

		$save_success = $edit_comment->save();

		if($save_success)
		{
			return $this->respondWithItem($edit_comment, new CommentTransformer, 'comment');
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

        //get comment by comment id
        $get_comment = Comment::where('id', $post['comment_id'])->first();

        if($get_comment == null)
        {
            return $this->errorNotFound('this comment cannot be found');
        }

        //delete all upvotes record on this comment
        $deleteUpvoteSuccess = ApiCommentController::deleteAllUpvotes($post['comment_id']);

        if(!$deleteUpvoteSuccess)
        {
            return $this->errorInternalError('server down');
        }

        //delete the comment by comment id
    	$delete_success = Comment::where('id', $post['comment_id'])->delete();

    	if($delete_success)
    	{
    		return $this->successNoContent();
    	}

    	return $this->errorInternalError('server down');
    }

    //upvote or downvote a comment
    public function upvote_or_downvote()
    {
    	if(!Input::has('comment_id'))
    	{
    		return $this->errorWrongArgs('comment_id field is required');
    	}

    	$post = Input::all();

    	$get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

        //get comment by comment id
        $get_comment = Comment::where('id', $post['comment_id'])->first();

        if($get_comment == null)
        {
            return $this->errorNotFound('comment not found');
        }

    	$upvote_exist = Upvote::where(['nus_id' => $get_nus_id, 'comment_id' => $post['comment_id']])->first();

    	if($upvote_exist == null)
    	{
            //increase comment voting
			$get_comment->vote = $get_comment->vote + 1;

			$save_success = $get_comment->save();

			if($save_success)
			{
                //insert new voting record
				$new_vote = new Upvote();

				$new_vote->nus_id = $get_nus_id;

				$new_vote->comment_id = $post['comment_id'];

				$create_success = $new_vote->save();

				if($create_success)
				{
					return $this->successNoContent();
				}
			}
    		
    	}
    	else
    	{
            //decrease comment voting
    		$get_comment->vote = $get_comment->vote - 1;

			$save_success = $get_comment->save();

			if($save_success)
			{
                //delete voting record
				$delete_success = $upvote_exist->delete();

				if($delete_success)
				{
					return $this->successNoContent();
				}
			}
    	}

        return $this->errorInternalError('server down');
    }

    //pin or unpin a comment as the best answer
    public function pin_unpin_comment()
    {
    	if(!Input::has('comment_id'))
    	{
    		return $this->errorWrongArgs('comment_id field is required');
    	}

    	$post = Input::all();

        //get comment by comment id
    	$get_comment = Comment::where('id', $post['comment_id'])->first();

    	if($get_comment != null)
    	{
    		$save_success = false;

            //check if the comment is the best answer
            //if no, pin as best answer, otherwise unpin
    		if($get_comment->best_answer)
    		{
    			$get_comment->best_answer = 0;

    			$save_success = $get_comment->save();
    		}
    		else
    		{
    			$get_comment->best_answer = 1;

    			$save_success = $get_comment->save();
    		}

    		if($save_success)
    		{
    			return $this->successNoContent();
    		}
    	}
        else
        {
            return $this->errorNotFound('this comment not found');
        }

    	return $this->errorInternalError('server down');
    }

    //delete all comment in this post
    public static function deleteAllComments($post_id)
    {
        $get_comment = Comment::where('post_id', $post_id)->get();

        if(sizeof($get_comment) <= 0)
        {
            return true;
        }

    	$delete_success = Comment::where('post_id', $post_id)->delete();

    	if(!$delete_success)
    	{
    		return false;
    	}

        return true;
    }

    //delete all upvote records when a comment is deleted
    public static function deleteAllUpvotes($comment_id)
    {
        $get_upvote = Upvote::where('comment_id', $comment_id)->get();

        if(sizeof($get_upvote) <= 0)
        {
            return true;
        }

        $delete_success = Upvote::where('comment_id', $comment_id)->delete();

        if(!$delete_success)
        {
            return false;
        }

        return true;
    }
}
