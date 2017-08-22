<?php

namespace App\Transformer;

use League\Fractal\TransformerAbstract;

class NotificationUpdateTransformer extends TransformerAbstract
{
	/**
	* turn this item object into a generic array
	* @return array
	*/
	public function transform($updateNo)
	{
		return [
			'new_notification' => $updateNo
		];
	}
}