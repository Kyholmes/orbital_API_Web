<?php

namespace App\Transformer;

use App\Post;
use App\Subscription_Post;
use League\Fractal\TransformerAbstract;

class SubscriptionPostTransformer extends TransformerAbstract
{
	protected $defaultIncludes = ['posts'];

	/**
	* turn this item object into a generic array
	* @return array
	*/
	public function transform(Subscription_Post $subscription_post)
	{
		return [
		];
	}

	/**
	* include post
	* @return League\Fractal\Resource\Item
	*/
	public function includePosts(Subscription_Post $subscription_post)
	{
		$posts = Post::where('id', $subscription_post->post_id)->first();

		return $this->item($posts, new PostsTransformer);
	}
}