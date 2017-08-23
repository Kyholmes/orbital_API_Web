<?php

namespace App\Transformer;

use App\Notification;
use App\Post;
use League\Fractal\TransformerAbstract;

class NotificationTransformer extends TransformerAbstract
{
	/**
	* turn this item object into a generic array
	* @return array
	*/
	public function transform(Notification $notification)
	{
		$get_post = Post::where('id', $notification->post_id)->first();

		return [
			'id' => $notification->id,
			'created_date' => $notification->created_date,
			'expired_date' => $notification->expired_date,
			'read' => $notification->read,
			'notification_type' => $notification->notification_type,
			'comment_id' => $notification->comment_id,
			'post_id' => $notification->post_id,
			'tag_id' => $notification->tag_id,
			'post_owner_nus_id' => $get_post->nus_id
		];
	}
}