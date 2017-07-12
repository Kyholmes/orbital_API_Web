<?php

namespace App\Transformer;

use App\Tag_Post;
use App\Tag;
use League\Fractal\TransformerAbstract;

class TagLabelTransformer extends TransformerAbstract
{
	public function transform(Tag_Post $tag_post)
	{
		$tag = Tag::where('id', $tag_post->tag_id)->first();

		return [
			'tag_id' => $tag->id,
			'tag_name' => $tag->tag
		];
	}
}