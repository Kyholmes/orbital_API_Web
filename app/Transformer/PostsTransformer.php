<?php

namespace App\Transformer;

use App\Post;
use App\Tag_Post;
use App\Tag;
use App\Upvote;
use App\Http\Controllers\API\AuthKeyController;
use League\Fractal\TransformerAbstract;

class PostsTransformer extends TransformerAbstract
{
	protected $defaultIncludes = ['tags'];

	public function transform(Post $post)
	{
		$get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

		$voted = 0;

		$check_vote = Upvote::where(['nus_id' => $get_nus_id, 'post_id' => $post->id])->first();

		if($check_vote != null)
		{
			$voted = 1;
		}

		return [
			'post_id' => $post->id,
			'title' => $post->title,
			'description' => $post->question_descrip,
			'date_updated' => $post->updated_date,
			'nus_id' => $post->nus_id,
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
}