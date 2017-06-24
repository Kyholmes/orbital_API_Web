<?php

namespace App\Transformer;

use App\User;
use League\Fractal\TranformerAbstract;

class TagOwnerTransformer extends TranformerAbstract
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