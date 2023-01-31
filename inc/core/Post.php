<?php

namespace Waboot\inc\core;

/**
 * A WP_Post wrapper. It is meant to be used to handle custom post types with many fields.
 */
class Post
{
	/**
	 * @var int|null
	 */
	private ?int $postId = null;
	/**
	 * @var \WP_Post|null
	 */
	private ?\WP_Post $wpPost = null;
	/**
	 * @var int
	 */
	private int $authorId;
	/**
	 * @var string|null
	 */
	private ?string $title = null;
	/**
	 * @var string|null
	 */
	private ?string $content = null;
	/**
	 * @var string|null
	 */
	private ?string $excerpt = null;
	/**
	 * @var array
	 */
	private array $metas = [];
	/**
	 * @var array
	 */
	private array $storedMetas = [];

	/**
	 * @param int|\WP_Post|null $post
	 * @throws PostException
	 */
	public function __construct($post = null)
	{
		if(\is_int($post)){
			$this->postId = $post;
		}elseif($post instanceof \WP_Post){
			$this->wpPost = $post;
			$this->postId = $post->ID;
		}elseif($post !== null){
			throw new PostException('Invalid param provided to Post constructor');
		}
	}

	/**
	 * @return int
	 */
	public function getPostId(): int
	{
		return $this->postId;
	}

	/**
	 * @return \WP_Post
	 * @throws PostException
	 */
	public function getWpPost(): \WP_Post
	{
		if(!isset($this->wpPost)){
			$post = get_post($this->getPostId());
			if(!$post instanceof \WP_Post){
				throw new PostException('No valid WP_Post found for ID: #'.$this->getPostId());
			}
			$this->wpPost = $post;
		}
		return $this->wpPost;
	}

	/**
	 * @return bool
	 */
	public function isNew(): bool
	{
		return $this->postId === null || $this->postId === 0;
	}

	/**
	 * @return string
	 */
	public function getPostType(): string
	{
		return 'post';
	}

	/**
	 * @return int|null
	 */
	public function getAuthorId(): ?int
	{
		return $this->authorId;
	}

	/**
	 * @param int $authorId
	 */
	public function setAuthorId(int $authorId): void
	{
		$this->authorId = $authorId;
	}

	/**
	 * @return string|null
	 */
	public function getTitle(): ?string
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle(string $title): void
	{
		$this->title = $title;
	}

	/**
	 * @return string|null
	 */
	public function getContent(): ?string
	{
		return $this->content;
	}

	/**
	 * @param string $content
	 */
	public function setContent(string $content): void
	{
		$this->content = $content;
	}

	/**
	 * @return string|null
	 */
	public function getExcerpt(): ?string
	{
		return $this->excerpt;
	}

	/**
	 * @param string $excerpt
	 */
	public function setExcerpt(string $excerpt): void
	{
		$this->excerpt = $excerpt;
	}

	/**
	 * @return void
	 */
	public function fetchStoredMetas(): void
	{
		if($this->isNew()){
			return;
		}
		$metas = get_post_meta($this->getPostId(),'',true);
		if(\is_array($metas) && !empty($metas)){
			$this->storedMetas = $metas;
		}
	}

	/**
	 * @return array
	 */
	public function getStoredMetas(): array
	{
		if($this->storedMetas === null){
			return [];
		}
		return $this->storedMetas;
	}

	/**
	 * @return array
	 */
	public function getMetas(): array
	{
		if($this->metas === null){
			return [];
		}
		return $this->metas;
	}

	/**
	 * @param array $metas
	 */
	public function setMetas(array $metas): void
	{
		$this->metas = $metas;
	}

	/**
	 * @param string $metaKey
	 * @param string $metaValue
	 * @return void
	 */
	public function setMeta(string $metaKey, string $metaValue): void
	{
		$currentMetas = $this->getMetas();
		$currentMetas[$metaKey] = $metaValue;
		$this->metas = $currentMetas;
	}

	/**
	 * @param array $args
	 * @return int
	 * @throws PostException
	 */
	public function save(array $args = []): int
	{
		$default = [
			'post_type' => $this->getPostType()
		];
		if($this->getAuthorId() !== null){
			$default['post_author'] = $this->getAuthorId();
		}else{
			$default['post_author'] = 0;
		}
		if($this->getTitle() === null || $this->getTitle() === ''){
			throw new PostException('Post->save(): no title specified');
		}
		$default['post_title'] = $this->getTitle();
		$default['post_content'] = $this->getContent() === null ? '' : $this->getContent();
		$default['post_excerpt'] = $this->getExcerpt() === null ? '' : $this->getExcerpt();
		$args = wp_parse_args($default,$args);

		if($this->isNew()){
			$r = wp_insert_post($args);
		}else{
			$r = wp_update_post($args);
		}
		if(\is_wp_error($r)){
			throw new PostException($r->get_error_message());
		}
		$postId = $r;
		$metasToSave = $this->getMetas();
		foreach ($metasToSave as $metaKey => $metaValue){
			update_post_meta($postId,$metaKey,$metaValue);
		}
		$this->postId = $r;
		return $r;
	}
}