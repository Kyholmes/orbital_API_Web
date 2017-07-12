<?php

namespace App\Transformer;

use App\Post;
use App\Tag_Post;
use App\Tag;
use League\Fractal\TransformerAbstract;

class PostsTransformer extends TransformerAbstract
{
	protected $defaultIncludes = ['tags'];

	public function transform(Post $post)
	{
		return [
			'post_id' => $post->id,
			'title' => $post->title,
			'description' => $post->question_descrip,
			'date_updated' => $post->updated_date
		];
	}

	/**
	* include post
	* @return League\Fractal\Resource\Item
	*/
	public function includeTags(Post $post)
	{
		$tags = Tag_Post::where('post_id', $post->id)->get();

		return $this->collection($tags, new TagLabelTransformer, 'tags');
	}
}