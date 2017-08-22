<?php

namespace App\Transformer;

use App\Achievement;
use League\Fractal\TransformerAbstract;

class AchievementTransformer extends TransformerAbstract
{
	/**
	* turn this item object into a generic array
	* @return array
	*/
	public function transform(Achievement $achievement)
	{
		return [
			'question_no' => $achievement->question_no,
			'answer_no' => $achievement->answer_no,
			'comment_no' => $achievement->comment_no,
			'points' => $achievement->points
		];
	}
}