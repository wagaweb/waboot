<?php

namespace Waboot\inc\core;

/**
 * A WP_Post wrapper. It is meant to be used to handle custom post types with many fields.
 */
class Post
{
    private int $postId;
    private \WP_Post $wpPost;

    /**
     * @param int|\WP_Post $post
     * @throws PostException
     */
    public function __construct($post)
    {
        if(\is_int($post)){
            $this->postId = $post;
        }elseif($post instanceof \WP_Post){
            $this->wpPost = $post;
            $this->postId = $post->ID;
        }else{
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
}