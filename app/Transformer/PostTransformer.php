<?php

namespace App\Transformer;

use App\Post;
use App\Tag_Post;
use App\Tag;
use App\User;
use App\Comment;
use App\Subscription_Post;
use App\Upvote;
use App\Http\Controllers\API\AuthKeyController;
use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract
{
	protected $defaultIncludes = ['tags', 'created_by', 'comments'];

	public function transform(Post $post)
	{
		$get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

		$subscribe = Subscription_Post::where(['post_id' => $post->id, 'nus_id' => $get_nus_id])->first();

		$subscribe_bool = false;

		if($subscribe != null)
		{
			$subscribe_bool = true;
		}

		$voted = false;

		$check_vote = Upvote::where(['nus_id' => $get_nus_id, 'post_id' => $post->id])->first();

		if($check_vote != null)
		{
			$voted = true;
		}

		return [
			'post_id' => $post->id,
			'title' => $post->title,
			'description' => $post->question_descrip,
			'date_updated' => $post->updated_date,
			'date_added' => $post->created_date,
			'image_link' => $post->img_link,
			'vote' => $post->vote,
			'subscription_no' => $post->subscribe_no,
			'subscribed' => $subscribe_bool,
			'voted' => $voted
		];
	}

	/**
	* include post
	* @return League\Fractal\Resource\Item
	*/
	public function includeTags(Post $post)
	{
		$tags = Tag_Post::where('post_id', $post->id)->get();

		return $this->collection($tags, new TagLabelTransformer, 'tag');
	}

	/**
	* include post owner
	* @return League\Fractal\Resource\Item
	*/
	public function includeCreatedBy(Post $post)
	{
		$post_owner = User::where('nus_id', $post->nus_id)->first();

		return $this->item($post_owner, new TagOwnerTransformer);
	}

	public function includeComments(Post $post)
	{
		$comments = Comment::where('post_id', $post->id)->orderBy('best_answer', 'desc')->orderBy('id', 'asc')->get();


		if($comments != null)
		{
			return $this->collection($comments, new CommentTransformer, 'comments');
		}

		return null;
	}
}