<?php

namespace App\Transformer;

use App\Tag;
use App\User;
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
		return [
			'tag_name' => $tag->tag,
			'description' => $tag->description,
			'last_update' => $tag->last_update,
			'subscribe_no' => $tag->subscribe_no,
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