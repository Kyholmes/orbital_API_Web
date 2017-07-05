<?php

namespace App\Transformer;

use App\User;
use League\Fractal\TransformerAbstract;

class TagOwnerTransformer extends TransformerAbstract
{
	/**
	* turn this item object into a generic arrray
	* @return array
	*/

	public function transform(User $user)
	{
		return [
			'nus_id' => $user->nus_id,
			'username' => $user->username,
			'name' => $user->name,
		];
	}
}