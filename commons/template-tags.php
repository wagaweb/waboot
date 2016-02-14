<?php

if(!function_exists('wbft_the_trimmed_excerpt')):
	/**
	 * A version of the_excerpt() that applies the trim function to the predefined excerpt as well
	 *
	 * @param bool $length
	 * @param bool|null $more
	 * @param null $post_id
	 * @param string $use is "content_also" then the content will be trimmed if the excerpt is empty
	 */
	function wbft_the_trimmed_excerpt($length = false,$more = null,$post_id = null, $use = "excerpt_only"){
		if(is_bool($length) && !$length){
			$excerpt_length = apply_filters( 'excerpt_length', 55 );
		}else{
			$excerpt_length = $length;
		}
		if(is_null($more)){
			$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
		}else{
			$excerpt_more = $more;
		}

		if(isset($post_id)){
			$post = get_post($post_id);
			if($use == "content_also" && $post->post_excerpt == ""){
				$text = apply_filters('the_content', $post->post_content);
			}else{
				$text = $post->post_excerpt;
			}
		}else{
			global $post;
			if($use == "content_also" && $post->post_excerpt == ""){
				$text = get_the_content();
			}else{
				$text = get_the_excerpt();
			}
		}

		echo  wp_trim_words($text,$excerpt_length,$excerpt_more);
	}
endif;