<?php

namespace App\Transformer;

use App\Tag;
use League\Fractal\TransformerAbstract;

class TagLabelTransformer extends TransformerAbstract
{
	public function transform(Tag $tag)
	{
		return [
			'tag_id' => $tag->id,
			'tag_name' => $tag->tag
		];
	}
}