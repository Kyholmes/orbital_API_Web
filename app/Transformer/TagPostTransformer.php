<?php

namespace App\Transformer;

use App\Post;
use App\Tag_Post;
use League\Fractal\TransformerAbstract;

class TagPostTransformer extends TransformerAbstract
{
	protected $defaultIncludes = ['posts'];

	/**
	* turn this item object into a generic array
	* @return array
	*/
	public function transform(Tag_Post $tag_post)
	{
		return [
		];
	}

	/**
	* include post
	* @return League\Fractal\Resource\Item
	*/
	public function includePosts(Tag_Post $tag_post)
	{
		$posts = Post::where('id', $tag_post->post_id)->first();

		return $this->item($post, new PostTransformer);
	}
}