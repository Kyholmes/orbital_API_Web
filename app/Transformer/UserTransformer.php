<?php

namespace App\Transformer;

use App\User;
use App\Access_Token;
use App\Subscription_Tag;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
	protected $defaultIncludes = ['access_token', 'subscription_tag'];

	/**
	* turn this item object into a generic array
	* @return array
	*/

	public function transform(User $user)
	{
		return [
			'nus_id' => $user->nus_id,
			'name' => $user->name,
			'username' => $user->username,
			'role' => $user->role,
		];
	}

	/**
	* include access token
	* @return League\Fractal\Resource\Item
	*/
	public function includeAccessToken(User $user)
	{
		$token = Access_Token::where('nus_id', $user->nus_id)->first();

		return $this->item($token, new AccessTokenTransformer);
	}

	/**
	* include subscription tag
	* @return League\Fractal\Resource\Collection
	*/
	public function includeSubscriptionTag(User $user)
	{
		$subscription_tag = Subscription_Tag::where('nus_id', $user->nus_id)->get();

		if($subscription_tag != null)
		{
			return $this->collection($subscription_tag, new SubscriptionTagTransformer);
		}
	}
}