<?php

namespace App\Transformer;

use App\Access_Token;
use League\Fractal\TransformerAbstract;

class AccessTokenTransformer extends TransformerAbstract
{
	/**
	* turn this item object into a generic array
	* @return array
	*/
	public function transform(Access_Token $token)
	{
		return [
			'token' => $token->token,
			'created_date' => $token->created_date,
			'expired_date' => $token->expired_date,
		];
	}
}