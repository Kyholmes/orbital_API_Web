<?php

namespace App\Transformer;

use App\Subscription_Tag;
use App\Tag;
use League\Fractal\TransformerAbstract;

class SubscriptionTagTransformer extends TransformerAbstract
{
	protected $defaultIncludes = ['tag'];

	/**
	* turn this item object into a generic array
	* @return array
	*/
	public function transform(Subscription_Tag $subscription_tag)
	{
		return [
			'tag_id' => $subscription_tag->tag_id,
			'last_visit' => $subscription_tag->last_visit,
		];
	}

	/**
	* include tag
	* @return League\Fractal\Resource\Item
	*/
	public function includeTag(Subscription_Tag $subscription_tag)
	{
		$tag = Tag::where('id', $subscription_tag->tag_id)->first();

		return $this->item($tag, new TagTransformer);
	}
}