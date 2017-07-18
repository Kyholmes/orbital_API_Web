<?php

namespace App\Transformer;

use App\User;
use League\Fractal\TransformerAbstract;

class CommentUserTransformer extends TransformerAbstract
{
	public function transform(User $user)
	{
		return [
			'nus_id' => $user->nus_id,
			'name' => $user->name,
			'username' => $user->username,
			'role' => $user->role
		];
	}
}