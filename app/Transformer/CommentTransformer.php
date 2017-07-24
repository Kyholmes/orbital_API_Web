<?php

namespace App\Transformer;


use App\Comment;
use App\User;
use App\Upvote;
use App\Http\Controllers\API\AuthKeyController;
use League\Fractal\TransformerAbstract;
class CommentTransformer extends TransformerAbstract
{
	protected $defaultIncludes = ['commenter', 'commentTo'];

	public function transform(Comment $comment)
	{
		$get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

		$voted = 0;

		$check_vote = Upvote::where(['nus_id' => $get_nus_id, 'comment_id' => $comment->id])->first();

		if($check_vote != null)
		{
			$voted = 1;
		}

		return [
			'comment_id' => $comment->id,
			'description' => $comment->description,
			'updated_date' => $comment->updated_date,
			'vote' => $comment->vote,
			'best_answer' => $comment->best_answer,
			'post_id' => $comment->post_id,
			'image_link' => $comment->img_link,
			'created_date' => $comment->created_date,
			'voted' => $voted
		];
	}

	/**
	* include commenter
	* @return League\Fractal\Resource\Item
	*/
	public function includeCommenter(Comment $comment)
	{
		$commenter = User::where('nus_id', $comment->nus_id)->first();

		return $this->item($commenter, new CommentUserTransformer);
	}

	/**
	* include commentTo
	* @return League\Fractal\Resource\Item
	*/
	public function includeCommentTo(Comment $comment)
	{
		$commentTo = User::where('nus_id', $comment->reply_to_nus_id)->first();

		if($commentTo != null)
		{
			return $this->item($commentTo, new CommentUserTransformer);
		}

		return null;
	}
}