<?php

namespace App\Http\Controllers\API;

// use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GetCurrentTimeController;
use App\Http\Controllers\API\ApiCommentController;
use App\Http\Controllers\API\ApiAchievementController;
use App\Post;
use App\Subscription_Post;
use App\Tag_Post;
use App\Upvote;
use App\Transformer\PostTransformer;
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

    //get post detail by post id
    public function get()
    {
    	if(!Input::has('post_id'))
    	{
    		return $this->errorWrongArgs('post_id field is required');
    	}

    	$post = Input::all();

    	$get_post = Post::where('id',$post['post_id'])->first();

    	if($get_post != null)
    	{
    		return $this->respondWithItem($get_post, new PostTransformer, 'post');
    	}

    	return $this->errorNotFound('Post not found');
    }

    //add new post
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

        //create new post object and assign value
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

    	$newPost->nus_id = $get_nus_id;

    	$addSuccess = $newPost->save();

        //if insert successful
    	if($addSuccess)
    	{
            //insert record into post tag table
    		$insertSuccess = ApiPostController::addPostToTag($post['tag_id'], $newPost->id);

            if(!$insertSuccess)
            {
                $newPost->delete();

                return $this->errorInternalError('server down');
            }

    		$current_time = GetCurrentTimeController::getCurrentTime();

            //add post subscription
    		$addNewSubSuccess = ApiPostController::addNewSubscriptionPost($get_nus_id, $newPost->id, $current_time);

            if(!$addNewSubSuccess)
            {
                return $this->errorInternalError('server down');
            }

            $updateAchievementSuccess = ApiAchievementController::updateAchievement(1, 1, $get_nus_id);

            return $this->successNoContent();
            
    	}

    	return $this->errorInternalError('server down');
    }

    //delete post by post id
    public function delete()
    {
    	if(!Input::has('post_id'))
    	{
    		return $this->errorWrongArgs('post_id field is required');
    	}

        $get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');
    	
    	$post = Input::all();

        //check if this post exist
        $get_post = Post::where('id', $post['post_id'])->first();

        if($get_post == null)
        {
            return $this->errorNotFound('this post cannot be found');
        }

        //delete all post subscription
    	$deletePostSubSuccess = ApiPostController::deleteSubscriptionPost($post['post_id']);

        if(!$deletePostSubSuccess)
        {
            return $this->errorInternalError('server down');
        }

        //delete tag post records
    	$deleteTagPostSuccess = ApiPostController::deleteTagPost($post['post_id']);

        if(!$deleteTagPostSuccess)
        {
            return $this->errorInternalError('server down');
        }

        //delete all the comments in this post
    	$deleteCommentSuccess = ApiCommentController::deleteAllComments($post['post_id']);

        if(!$deleteCommentSuccess)
        {
            return $this->errorInternalError('server down');
        }

        $deleteVoteSuccess = ApiPostController::deleteVote($post['post_id']);

        if(!$deleteVoteSuccess)
        {
            return $this->errorInternalError('server down');
        }

        //delete the post by post id
    	$delete_post = Post::where('id', $post['post_id'])->delete();

    	if($delete_post)
    	{
            $updateAchievementSuccess = ApiAchievementController::updateAchievement(1, -1, $get_nus_id);

    		return $this->successNoContent();
    	}

    	return $this->errorInternalError('server down');
    }

    public function edit()
    {
    	$v = Validator::make(Input::all(), [
    			'post_id' => 'required',
    			'title' => 'required',
    			'description' => 'required_without:image_link'
    	]);

    	if($v->fails())
    	{
    		return $this->errorWrongArgs($v->errors());
    	}

    	$post = Input::all();

        //get the post by post id and edit values
    	$get_post = Post::where('id', $post['post_id'])->first();

        if($get_post == null)
        {
            return $this->errorNotFound('this post cannot be found');
        }

    	$get_post->title = $post['title'];

    	$get_post->question_descrip = $post['description'];

    	if(Input::has('image_link'))
    	{
    		$get_post->img_link = $post['image_link'];
    	}
    	
    	$save_success = $get_post->save();

    	if($save_success)
    	{
    		return $this->successNoContent();
    	}

    	return $this->errorInternalError('server down');
    }

    //upvote or downvote a post
    public function upvote_or_downvote()
    {
    	if(!Input::has('post_id'))
    	{
    		return $this->errorWrongArgs('post_id field is required');
    	}

        // if(!Input::has('owner_nus_id'))
        // {
        //     return $this->errorWrongArgs('post owner nus_id field is required');
        // }


    	$post = Input::all();

    	$get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

        // $owner_nus_id = $post['owner_nus_id'];

        //get post by post id
        $get_post = Post::where('id', $post['post_id'])->first();

        if($get_post == null)
        {
            return $this->errorNotFound('post not found');
        }

        //check if user has voted before
    	$upvote_exist = Upvote::where(['nus_id' => $get_nus_id, 'post_id' => $post['post_id']])->first();

        //if user never vote this post before
    	if($upvote_exist == null)
    	{
            //increase voting of the post
			$get_post->vote = $get_post->vote + 1;

			$save_success = $get_post->save();

			if($save_success)
			{
                //insert new user voting record
				$new_vote = new Upvote();

				$new_vote->nus_id = $get_nus_id;

				$new_vote->post_id = $post['post_id'];

				$create_success = $new_vote->save();

				if($create_success)
				{
                    $updateAchievementSuccess = ApiAchievementController::updateAchievement(2, 1, $get_nus_id);

                    $updateAchievementSuccess = ApiAchievementController::updateAchievement(2, 1, $get_post->nus_id);

					return $this->successNoContent();
				}
			}
    	}
        //if user voted before, downvote the post
    	else
    	{
            //decrease voting of the post
    		$get_post->vote = $get_post->vote - 1;

			$save_success = $get_post->save();

			if($save_success)
			{
                //delete the voting records
				$delete_success = $upvote_exist->delete();

				if($delete_success)
				{
                    $updateAchievementSuccess = ApiAchievementController::updateAchievement(2, -1, $get_nus_id);

                    $updateAchievementSuccess = ApiAchievementController::updateAchievement(2, -1, $get_post->nus_id);

					return $this->successNoContent();
				}
			}
    	}

        return $this->errorInternalError('server down');
    }

    //add records to post tag when a new post is created
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
    		return false;
    	}

        return true;
    }

    //add new post subscription
    public static function addNewSubscriptionPost($nus_id, $post_id, $current_time)
    {   
        //create new post subscription object and assign values
    	$new_subscription_post = new Subscription_Post();

    	$new_subscription_post->post_id = $post_id;

    	$new_subscription_post->nus_id = $nus_id;

    	$new_subscription_post->last_visit = $current_time;

    	$addSuccess = $new_subscription_post->save();

    	if(!$addSuccess)
    	{
    		return false;
    	}

        return true;
    }

    //delete all post subscriptions
    public function deleteSubscriptionPost($post_id)
    {
    	$subscription_post = Subscription_Post::where('post_id', $post_id)->delete();

    	if(!$subscription_post)
    	{
    		return false;
    	}

        return true;
    }

    //delete tag post records
    public function deleteTagPost($post_id)
    {
    	$tag_post = Tag_Post::where('post_id', $post_id)->delete();

    	if(!$tag_post)
    	{
    		return false;
    	}

        return true;
    }

    //delete vote records
    public function deleteVote($post_id)
    {
        $get_post_vote = Upvote::where('post_id', $post_id)->get();

        if(sizeof($get_post_vote) <= 0)
        {
            return true;
        }

        $post_vote = Upvote::where('post_id', $post_id)->delete();

        if(!$post_vote)
        {
            return false;
        }

        return true;
    }
}
