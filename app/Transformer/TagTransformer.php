<?php

namespace App\Transformer;

use App\Tag;
use App\User;
use App\Http\Controllers\API\AuthKeyController;
use App\Subscription_Tag;
use League\Fractal\TransformerAbstract;

class TagTransformer extends TransformerAbstract
{
	protected $defaultIncludes = ['created_by'];

	/**
	* turn this item object into a generic array
	* @return array
	*/
	public function transform(Tag $tag)
	{
		$get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

		$tag_sub = Subscription_Tag::where(['nus_id' => $get_nus_id, 'tag_id' => $tag->id])->first();

		$subscribed = false;

		if($tag_sub != null)
		{
			$subscribed = true;
		}

		return [
			'tag_id' => $tag->id,
			'tag_name' => $tag->tag,
			'description' => $tag->description,
			'last_update' => $tag->last_update,
			'subscribe_no' => $tag->subscribe_no,
			'tag_status' => $tag->status,
			'subscribed' => $subscribed
		];
	}

	/**
	* include tag owner
	* @return League\Fractal\Resource\Item
	*/
	public function includeCreatedBy(Tag $tag)
	{
		$tag_owner = User::where('nus_id', $tag->created_by)->first();

		return $this->item($tag_owner, new TagOwnerTransformer);
	}
}