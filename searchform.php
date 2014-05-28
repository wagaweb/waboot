<?php
/**
 * The template for displaying search forms in Waboot
 *
 * @package Waboot
 * @since Waboot 1.0
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<!-- <span class="screen-reader-text"><?php _ex( 'Search:', 'label', 'waboot' ); ?></span> -->
		<input type="search" class="search-field form-control" placeholder="<?php echo esc_attr_x( 'Search &hellip;', 'placeholder', 'waboot' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	</label>
	<input type="submit" class="search-submit btn btn-default" value="<?php echo esc_attr_x( 'Search', 'submit button', 'waboot' ); ?>">
</form>