<div class="slidein sidesearch" data-slidein-search data-slidein-toggle="#slidein-search__toggle">
	<a data-slidein-close><i class="far fa-times"></i></a>

	<form id="searchform" class="search-form" role="search" action="<?php echo site_url(); ?>" method="get">
		<input id="s" name="s" type="text" placeholder="<?php esc_attr_e( 'Cosa stai cercando?', LANG_TEXTDOMAIN ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
		<button class="btn" id="searchsubmit" type="submit" name="submit"><i class="far fa-search"></i></button>
	</form>
</div>