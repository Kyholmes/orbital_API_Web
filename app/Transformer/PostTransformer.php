<?php

namespace App\Transformer;

use App\Post;
use App\Tag_Post;
use App\Tag;
use App\User;
use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract
{
	protected $defaultIncludes = ['tags', 'created_by'];

	public function transform(Post $post)
	{
		return [
			'post_id' => $post->id,
			'title' => $post->title,
			'description' => $post->question_descrip,
			'date_updated' => $post->updated_date,
			'date_added' => $post->created_date,
			'image_link' => $post->img_link,
			'vote' => $post->vote,
			'subscription_no' => $post->subscribe_no
		];
	}

	/**
	* include post
	* @return League\Fractal\Resource\Item
	*/
	public function includeTags(Post $post)
	{
		$tags = Tag_Post::where('post_id', $post->id)->get();

		return $this->collection($tags, new TagLabelTransformer, 'tags');
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
}